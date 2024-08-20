<?php

namespace App\MessageHandler;

use App\Message\ProcessScanMessage;
use App\Service\DebrickedService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Scan;
use App\Constants\Status;
use App\Constants\General;
use App\Entity\UploadBatch;
use App\Service\NotificationService;
use Psr\Log\LoggerInterface;

/**
 * Handles processing of scan messages.
 */
class ProcessScanMessageHandler
{
    private DebrickedService $debrickedService;
    private EntityManagerInterface $entityManager;
    private NotificationService $notificationService;
    private LoggerInterface $logger;

    /**
     * @param EntityManagerInterface $entityManager The entity manager for database operations.
     * @param DebrickedService $debrickedService The service for interacting with the Debricked API.
     * @param NotificationService $notificationService The service for sending notifications.
     * @param LoggerInterface $logger The logger for recording events.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        DebrickedService $debrickedService,
        NotificationService $notificationService,
        LoggerInterface $logger,
    ) {
        $this->entityManager = $entityManager;
        $this->debrickedService = $debrickedService;
        $this->notificationService = $notificationService;
        $this->logger = $logger;
    }

    /**
     * Processes a scan message and updates scan status.
     *
     * @param ProcessScanMessage $message The message containing scan information.
     */
    public function __invoke(ProcessScanMessage $message)
    {
        try{
            $scanId = $message->getScanId();
            $scan = $this->entityManager->getRepository(Scan::class)->findOneBy(['ci_upload_id' => $scanId]);
            
            $res = $this->debrickedService->checkScanStatus($scanId);
            
            if ($res['status_code'] == 200) {
                $data = $res['data'];
                $scan->setPercentage((float)$data['progress']);
                $scan->setVulnerabilitiesFound($data['vulnerabilitiesFound']);
                $scan->setUnaffectedVulnerabilitiesFound($data['unaffectedVulnerabilitiesFound']);
                $scan->setAutomationsAction($data['automationsAction']);
                $scan->setPolicyEngineAction($data['policyEngineAction']);
                
                if ($data['progress'] >= 100) {
                    $batch = $scan->getUpload()->getUploadBatch();
                    $scan->setStatus(Status::SCANNING_COMPLETED);
                    $scan->setRemainingScans(0);
                    $scan->setEstimatedDaysLeft(0);
                    $this->updateBatchStatus($batch);
                    
                    if ($data['vulnerabilitiesFound'] > 4) {
                        $messageBindParams = array(
                            $batch->getBatchName(),
                            $scan->getUpload()->getFileName(),
                            $data['vulnerabilitiesFound']
                        );
                        $this->notificationService->sendNotification($batch->getUser(), General::NOTIFICATION_FOUND_VULNERABILITIES, $messageBindParams);
                    }
                }
            } else {
                $scan->setStatus(Status::FAILED);
                $msg = (isset($res['data']) && is_array($res['data'])) ? json_encode($res['data']) : $res['data'];
                $scan->setMessage($msg);
            }

            $this->entityManager->persist($scan);
            $this->entityManager->flush();
        }catch(\Exception $exception){
            $this->logger->error('Error while checking the scanning results from Debricked: ' . $exception->getMessage(), ['exception' => $exception]);
        }
    }

    /**
     * Updates the status of the upload batch based on scanned files.
     *
     * @param UploadBatch $uploadBatch The upload batch to update.
     */
    private function updateBatchStatus(UploadBatch $uploadBatch):void
    {
        $uploadBatch = $this->entityManager->getRepository(UploadBatch::class)->find($uploadBatch->getId());
        $totalScanned = $uploadBatch->getTotalScanned() + 1;
        $totalUploadedFiles = $uploadBatch->getTotalUploadedFiles();
        $uploadBatch->setTotalScanned($totalScanned);

        if ($totalScanned == $totalUploadedFiles) {
            $uploadBatch->setStatus(Status::SCANNING_COMPLETED);
        }

        $this->entityManager->persist($uploadBatch);
        $this->entityManager->flush();
    }
}
