<?php

namespace App\MessageHandler;

use App\Message\UploadMessage;
use App\Constants\General;
use Psr\Log\LoggerInterface;
use App\Entity\Upload;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\UploadBatch;
use App\Service\DebrickedService;
use App\Constants\Status;
use App\Entity\Scan;
use App\Service\NotificationService;


/**
 * Handles processing of upload messages and manages the upload process.
 */
class UploadMessageHandler
{
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;
    private DebrickedService $debrickedService;
    private NotificationService $notificationService;

    /**
     * @param LoggerInterface $logger The logger for recording events.
     * @param EntityManagerInterface $entityManager The entity manager for database operations.
     * @param DebrickedService $debrickedService The service for interacting with the Debricked API.
     * @param NotificationService $notificationService The service for sending notifications.
     */
    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        DebrickedService $debrickedService,
        NotificationService $notificationService
    ) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->debrickedService = $debrickedService;
        $this->notificationService = $notificationService;
    }

    /**
     * Processes an upload message and handles file uploads and scanning.
     *
     * @param UploadMessage $message The message containing upload batch information.
     * 
     * @throws \Exception
     */
    public function __invoke(UploadMessage $message)
    {
        try{
            $uploadBatch = $message->getBatch();
            $uploadBatch = $this->entityManager->getRepository(UploadBatch::class)->find($uploadBatch->getId());

            if (!$uploadBatch) {
                throw new \Exception('UploadBatch not found');
            }

            $messageBindParams = array(
                $uploadBatch->getBatchName()
            );
            $this->notificationService->sendNotification($uploadBatch->getUser(), General::NOTIFICATION_UPLOAD_IN_PROGRESS, $messageBindParams);

            // Update the UploadBatch status to "In Progress"
            $uploadBatch->setStatus(Status::UPLOAD_IN_PROGRESS);
            $this->entityManager->persist($uploadBatch);
            $this->entityManager->flush();

            // Fetch uploads related to this batch
            $uploads = $this->entityManager->getRepository(Upload::class)->findByUploadBatch($uploadBatch);

            $completed = 0;
            $failed = 0;

            foreach ($uploads as $upload) {
                $res = $this->debrickedService->uploadDependencyFile(
                    $upload->getFilePath(),
                    $upload->getFileName(),
                    $uploadBatch->getBatchName() . ' ' . $upload->getFileName(),
                    $uploadBatch->getBatchName()
                );
                if ($res['status_code'] == 200) {
                    $upload->setStatus(Status::UPLOAD_COMPLETED);
                    $this->initiateScanning($res['data'], $upload);
                    $completed++;
                } else {
                    $upload->setStatus(Status::FAILED);
                    $msg = is_array($res['data']) ? json_encode($res['data']) : $res['data'];
                    $upload->setMessage($msg);
                    $failed++;
                    $messageBindParams = array(
                        $uploadBatch->getBatchName(),
                        $upload->getFileName(),
                        $msg
                    );
                    $this->notificationService->sendNotification($uploadBatch->getUser(), General::NOTIFICATION_UPLOAD_FAILED, $messageBindParams);
                }

                $this->entityManager->persist($upload);

                // Unlink (delete) the file after processing
                if (file_exists($upload->getFilePath())) {
                    unlink($upload->getFilePath());
                }
            }

            $uploadBatch->setTotalUploadedFiles($completed);
            $uploadBatch->setTotalFailedUpload($failed);
            $uploadBatch->setStatus(Status::SCANNING_IN_PROGRESS);
            $this->entityManager->persist($uploadBatch);
            $this->entityManager->flush();
        }catch(\Exception $exception){
            $this->logger->error('Error while uploading the files to Debricked: ' . $exception->getMessage(), ['exception' => $exception]);
        }
    }

    /**
     * Initiates the scanning process for a given upload.
     *
     * @param array $data The data required to initiate scanning.
     * @param Upload $upload The upload entity associated with the scan.
     */
    private function initiateScanning(array $data, Upload $upload): void
    {
        $scan = new Scan();
        $scan->setCiUploadId($data['ciUploadId']);
        $scan->setUploadProgramsFileId($data['uploadProgramsFileId']);
        $scan->setTotalScans($data['totalScans']);
        $scan->setRemainingScans($data['remainingScans']);
        $scan->setEstimatedDaysLeft($data['estimatedDaysLeft']);
        $scan->setStatus(Status::SCANNING_IN_QUEUE);
        $scan->setCreatedAt();
        $scan->setUpdatedAt();
        $scan->setUpload($upload);
        $this->entityManager->persist($scan);
        $this->entityManager->flush();

        $res = $this->debrickedService->initializeScanning($data['ciUploadId']);
        if ($res['status_code'] == 200) {
            $scan->setStatus(Status::SCANNING_IN_PROGRESS);
            $scan->setRepositoryId($res['data']['repositoryId']);
            $scan->setCommitId($res['data']['commitId']);
        } else {
            $scan->setStatus(Status::FAILED);
            $msg = (isset($res['data']) && is_array($res['data'])) ? json_encode($res['data']) : $res['data'];
            $scan->setMessage($msg);
        }
        $this->entityManager->persist($scan);
        $this->entityManager->flush();
    }
}
