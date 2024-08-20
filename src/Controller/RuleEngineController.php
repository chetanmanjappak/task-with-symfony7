<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\ProcessUploadService;
use Psr\Log\LoggerInterface;
use App\DTO\FileUploadDTO;

/**
 * Handles file upload requests.
 */
class RuleEngineController extends AbstractController
{
    private ProcessUploadService $processUploadService;
    private LoggerInterface $logger;

    public function __construct(ProcessUploadService $processUploadService, LoggerInterface $logger)
    {
        $this->processUploadService = $processUploadService;
        $this->logger = $logger;
    }

    /**
     * Handles the file upload process.
     *
     * @param Request $request The HTTP request object containing the files,email,slack channel name, and Batch name to process.
     * @param ValidatorInterface $validator The validator service for validating the DTO.
     *
     * @return JsonResponse A response object indicating the result of the upload process, including status and message.
     */
    #[Route('/api/upload-files', name: 'upload-files', methods: ['POST'])]
    public function uploadFiles(Request $request, ValidatorInterface $validator): JsonResponse
    {
        // Initialize DTO
        $fileUploadDTO = new FileUploadDTO();
        
        $uploadedFiles = $request->files->get('files', []);
        if (!is_array($uploadedFiles)) {
            $uploadedFiles = [$uploadedFiles];
        }

        $fileUploadDTO->files = $uploadedFiles;
        $fileUploadDTO->email = $request->request->get('email');
        $fileUploadDTO->slackChannel = $request->request->get('slack_channel');
        $fileUploadDTO->batchName = $request->request->get('batch_name');

        // Validate the DTO
        $errors = $validator->validate($fileUploadDTO);

        if (count($errors) > 0) {
            // Handle validation errors
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return new JsonResponse([
                'status' => 'error',
                'message' => implode(', ', $errorMessages)
            ], Response::HTTP_BAD_REQUEST);
        }

        $uploadDirectory = $this->getParameter('kernel.project_dir').'/public/uploads';
        try {
            $this->processUploadService->processUpload(
                $fileUploadDTO->files,
                $uploadDirectory,
                $fileUploadDTO->batchName,
                $fileUploadDTO->email,
                $fileUploadDTO->slackChannel
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to upload files: ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Error while uploading files.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Files uploaded and processing started.'
        ], Response::HTTP_OK);
    }
}
