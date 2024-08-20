<?php

namespace App\Service;

use App\Constants\Status;
use App\Entity\Upload;
use App\Entity\UploadBatch;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\BackgroundUploadService;

/**
 * Service responsible for handling file uploads and processing.
 */
class ProcessUploadService
{
    private EntityManagerInterface $entityManager;
    private BackgroundUploadService $backgroundUploadService;

    /**
     * @param EntityManagerInterface $entityManager The entity manager for database operations.
     * @param BackgroundUploadService $backgroundUploadService The service for background processing of uploads.
     */
    public function __construct(EntityManagerInterface $entityManager, BackgroundUploadService $backgroundUploadService)
    {
        $this->entityManager = $entityManager;
        $this->backgroundUploadService = $backgroundUploadService;
    }

    /**
     * Processes uploaded files and manages the upload batch.
     *
     * @param UploadedFile[] $files An array of uploaded files.
     * @param string $uploadDir The directory where files will be uploaded.
     * @param string $batchName The name of the upload batch.
     * @param string $userEmail The email of the user uploading the files.
     * @param string|null $slackId Optional Slack ID for notifications.
     * 
     * @throws \Exception If an error occurs during the upload process.
     */
    public function processUpload(array $files, string $uploadDir, string $batchName, string $userEmail, ?string $slackId = null): void
    {
        $this->entityManager->getConnection()->beginTransaction();
        try {
            
            $uploadBatch = $this->createUploadBatch(count($files), $batchName, $userEmail, $slackId);
          
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $filePath = $this->uploadFile($file, $uploadDir);
                    $this->createUpload($filePath, $file->getClientOriginalName(), $file->getClientMimeType(), $uploadBatch);
                }
            }
            $this->entityManager->getConnection()->commit();
            $this->backgroundUploadService->processBackgroundUpload($uploadBatch);
        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollBack();
            dd($e);
            throw $e;
        }
    }

    /**
     * Creates an upload batch entity.
     *
     * @param int $totalFiles The total number of files in the batch.
     * @param string $batchName The name of the batch.
     * @param string $userEmail The email of the user associated with the batch.
     * @param string|null $slackId Optional Slack ID for notifications.
     * 
     * @return UploadBatch The created upload batch entity.
     */
    public function createUploadBatch(int $totalFiles, string $batchName, string $userEmail, ?string $slackId = null): UploadBatch
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userEmail]);
       
        if (!$user) {
            $user = new User();
            $user->setEmail($userEmail);
            $user->setSlackChannel($slackId);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
        
        $uploadBatch = new UploadBatch();
        $uploadBatch->setBatchName($batchName);
        $uploadBatch->setTotalReceivedFiles($totalFiles);
        $uploadBatch->setStatus(Status::UPLOAD_IN_QUEUE);
        $uploadBatch->setCreatedAt();
        $uploadBatch->setUpdatedAt();
        
        $uploadBatch->setUser($user);
       
        $this->entityManager->persist($uploadBatch);
        $this->entityManager->flush();

        return $uploadBatch;
    }

    /**
     * Handles the file upload process.
     *
     * @param UploadedFile $file The file to be uploaded.
     * @param string $uploadDir The directory where the file will be uploaded.
     * 
     * @return string The path of the uploaded file.
     */
    public function uploadFile(UploadedFile $file, string $uploadDir): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = uniqid() . '-' . $originalFilename . '.' . $file->guessExtension();
        $destination = $uploadDir . '/public/uploads';
        $file->move($destination, $newFilename);
        return $destination . '/' . $newFilename;
    }

    /**
     * Creates an upload entity for a file.
     *
     * @param string $filePath The path where the file is stored.
     * @param string $fileName The name of the file.
     * @param string $fileType The MIME type of the file.
     * @param UploadBatch $uploadBatch The upload batch associated with the file.
     */
    public function createUpload(string $filePath, string $fileName, string $fileType, UploadBatch $uploadBatch): void
    {
        $uploadedAt = new \DateTimeImmutable();
        $upload = new Upload();
        $upload->setFileName($fileName);
        $upload->setFilePath($filePath);
        $upload->setStatus(Status::UPLOAD_IN_QUEUE);
        $upload->setFileType($fileType);
        $upload->setUploadAt($uploadedAt);
        $upload->setUploadBatch($uploadBatch);
        $this->entityManager->persist($upload);
        $this->entityManager->flush();
    }
}
