<?php

declare(strict_types=1);

namespace App\Domain\Api\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[Autoconfigure(lazy: true)]
class ApiService
{
    private const MAX_RETRIES = 10;
    private HttpClientInterface $client;
    private LoggerInterface $logger;
    private string $apiUrl;

    public function __construct(
        HttpClientInterface $client,
        LoggerInterface $logger,
        #[Autowire('%base.api.url%')] string $apiUrl
    ) {
        $this->client = $client;
        $this->logger = $logger;
        $this->apiUrl = $apiUrl;
    }

    /**
     * @throws \Exception
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     * @param array{'name': string, 'email': string} $payload
     * @return array{'name': string, 'email': string}
     */
    public function randomPostRequest(array $payload, int $attempt = 1): array
    {
        $apiUrl = $this->apiUrl . '/post-endpoint';

        try {
            $this->writeInfoLog(
                "Attempt $attempt: Sending POST request to $apiUrl",
                [
                    'payload' => $payload,
                ]
            );

            $response = $this->post($apiUrl, $payload);
            $statusCode = $response->getStatusCode();
            $data = $response->toArray();

            if ($statusCode < 200 || $statusCode >= 300) {
                $this->writeWarningLog(
                    "Attempt $attempt: Received status code $statusCode",
                    [
                        'url' => $apiUrl,
                        'statusCode' => $statusCode,
                    ]
                );
                if ($attempt < self::MAX_RETRIES) {
                    sleep(2 ** $attempt);
                    return $this->randomPostRequest($payload, $attempt + 1);
                }

                $this->writeErrorLog(
                    "API request failed after $attempt attempts",
                    [
                        'url' => $apiUrl,
                        'statusCode' => $statusCode,
                    ]
                );

                throw new \Exception("API request failed with status code: $statusCode after $attempt attempts");
            }

            $this->writeSuccessLog(
                "Request successful on attempt $attempt",
                [
                'url' => $apiUrl,
                'statusCode' => $statusCode,
                'response' => $data,
                ]
            );

            return $data;
        } catch (TransportExceptionInterface $e) {
            $this->writeExceptionLog(
                "Transport exception occurred: " . $e->getMessage(),
                [
                    'url' => $apiUrl,
                    'payload' => $payload,
                    'response' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    /**
     * @param array<string, string|int> $payload
     * @throws TransportExceptionInterface
     */
    private function post(string $apiUrl, array $payload): ResponseInterface
    {
        return $this->client->request('POST', $apiUrl, [
            'json' => $payload,
        ]);
    }

    /**
     * @param array{'payload': array<string, string|int>} $log
     */
    private function writeInfoLog(string $message, array $log): void
    {
        $this->logger->info(
            $message,
            $log
        );
    }

    /**
     * @param array{'url': string, 'statusCode': int, 'response': string} $log
     */
    private function writeSuccessLog(string $message, array $log): void
    {
        $this->logger->info(
            $message,
            [
                'url' => $log['url'],
                'statusCode' => $log['statusCode'],
                'response' => $log['response']
            ]
        );
    }

     /**
     * @param array{'url': string, 'statusCode': int} $log
     */
    private function writeWarningLog(string $message, array $log): void
    {
        $this->logger->warning(
            $message,
            [
                'url' => $log['url'],
                'statusCode' => $log['statusCode'],
            ]
        );
    }

    /**
     * @param array{'url': string, 'statusCode': int, 'response': string} $log
     */
    private function writeErrorLog(string $message, array $log): void
    {
        $this->logger->error(
            $message,
            [
                'url' => $log['url'],
                'statusCode' => $log['statusCode'],
            ]
        );
    }

    /**
     * @param array{'url': string, 'payload': array<string, string|int>, 'response': string} $log
     */
    private function writeExceptionLog(string $message, array $log): void
    {
        $this->logger->error(
            $message,
            [
                'url' => $log['url'],
                'payload' => $log['payload'],
                'response' => $log['response'],
            ]
        );
    }
}
