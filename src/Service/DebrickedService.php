<?php

namespace App\Service;

use GuzzleHttp\Client;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Log\LoggerInterface;
use \Exception;

/**
 * Service class for interacting with the Debricked API.
 */
class DebrickedService
{
    private Client $guzzleHttp;
    private CacheInterface $cache;
    private LoggerInterface $logger;

    /**
     * @param Client $guzzleHttp The HTTP client for making requests to the Debricked API.
     * @param CacheInterface $cache The cache service for storing and retrieving API tokens.
     * @param LoggerInterface $logger The logger for recording events.
     */
    public function __construct(Client $guzzleHttp, CacheInterface $cache,LoggerInterface $logger)
    {
        $this->guzzleHttp = $guzzleHttp;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * Retrieves the API token from cache or generates a new one if not present.
     *
     * @return string The API token.
     */
    public function getToken(): string
    {
        return $this->cache->get('api_token', function (ItemInterface $item) {
            $item->expiresAfter(30);
            return $this->generateNewToken();
        });
    }

    /**
     * Uploads a dependency file to the Debricked API.
     *
     * @param string $filePath The path to the file being uploaded.
     * @param string $fileName The name of the file.
     * @param string $commitName The commit name associated with the file.
     * @param string $repositoryName The repository name associated with the file.
     *
     * @return array The response status code and data.
     */
    public function uploadDependencyFile(string $filePath, string $fileName, string $commitName, string $repositoryName): array
    {
        try{
            $response = $this->guzzleHttp->post(getenv('DEBRICKED_ENDPOINT').'uploads/dependencies/files', [
                'headers' => [
                    'accept' => '*/*',
                    'Authorization' => 'Bearer ' . $this->getToken(),
                ],
                'multipart' => [
                    [
                        'name' => 'commitName',
                        'contents' => $commitName,
                    ],
                    [
                        'name' => 'repositoryName',
                        'contents' => $repositoryName,
                    ],
                    [
                        'name' => 'fileData',
                        'contents' => file_get_contents($filePath),
                        'filename' => $fileName,
                        'headers' => [
                            'Content-Type' => 'application/octet-stream',
                        ],
                    ],
                ],
            ]);
    
            return [
                'status_code' => $response->getStatusCode(),
                'data' => json_decode($response->getBody()->getContents(), true),
            ];
        }catch(Exception $exception){
            $inputData = array('filePath'=>$filePath, 'fileName'=> $fileName, 'commitName'=> $commitName, 'repositoryName'=> $repositoryName);
            $this->logger->error('Failed to upload FIles to debricked: ' . $exception->getMessage(), ['inputData'=>$inputData,'exception' => $exception]);
            return [
                'status_code' => $exception->getCode(),
                'data' => $exception->getMessage(),
            ];
        }
        
    }

    /**
     * Initializes scanning for a given upload.
     *
     * @param int $ciUploadId The unique identifier for the CI upload.
     *
     * @return array The response status code and data.
     */
    public function initializeScanning(int $ciUploadId): array
    {
        try{
            $response = $this->guzzleHttp->request('POST', getenv('DEBRICKED_ENDPOINT').'finishes/dependencies/files/uploads', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Bearer ' . $this->getToken(),
                ],
                'form_params' => [
                    'ciUploadId' => $ciUploadId,
                    'returnCommitData' => 'false',
                ],
            ]);

            return [
                'status_code' => $response->getStatusCode(),
                'data' => json_decode($response->getBody()->getContents(), true),
            ];
        }catch(Exception $exception){
            $inputData = array('ciUploadId'=>$ciUploadId);
            $this->logger->error('Failed to inititialize the scan: ' . $exception->getMessage(), ['inputData'=>$inputData,'exception' => $exception]);
            return [
                'status_code' => $exception->getCode(),
                'data' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Generates a new API token.
     *
     * @return string The new API token.
     */
    private function generateNewToken(): string
    {
        $tokenresponse = $this->guzzleHttp->request('POST', getenv('DEBRICKED_TOKEN_ENDPOINT'), [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                '_username' => getenv('DEBRICKED_USER'),
                '_password' => getenv('DEBRICKED_PWD'),
            ],
        ]);

        $token = $tokenresponse->getBody()->getContents();
        $newToken = json_decode($token, true);

        return $newToken['token'];
    }

    /**
     * Checks the status of a scan.
     *
     * @param string $scanId The unique identifier for the scan.
     *
     * @return array The response status code and data.
     */
    public function checkScanStatus(string $scanId): array
    {
        try{
            $response = $this->guzzleHttp->get(getenv('DEBRICKED_ENDPOINT').'ci/upload/status?ciUploadId='.$scanId, [
                'headers' => [
                    'accept' => '*/*',
                    'Authorization' => 'Bearer ' . $this->getToken(),
                ],
            ]);

            return [
                'status_code' => $response->getStatusCode(),
                'data' => json_decode($response->getBody()->getContents(), true),
            ];
        }catch(Exception $exception){
            $inputData = array('scanId'=>$scanId);
            $this->logger->error('Failed to check the Scan Status from debricked: ' . $exception->getMessage(), ['inputData'=>$inputData,'exception' => $exception]);
            return [
                'status_code' => $exception->getCode(),
                'data' => $exception->getMessage(),
            ];
        }
    }
}
