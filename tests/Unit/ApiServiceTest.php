<?php

declare(strict_types=1);

namespace App\Tests\Domain\Api\Service;

use App\Domain\Api\Service\ApiService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiServiceTest extends TestCase
{
    private HttpClientInterface $httpClientMock;
    private LoggerInterface $loggerMock;
    private ApiService $apiService;

    protected function setUp(): void
    {
        $this->httpClientMock = $this->createMock(HttpClientInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->apiService = new ApiService(
            $this->httpClientMock,
            $this->loggerMock,
            'https://example.com'
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function testRandomPostRequestSuccess(): void
    {
        $payload = ['key1' => 'value1', 'key2' => 'value2'];
        $responseMock = $this->createMock(ResponseInterface::class);

        // Simulate a successful response
        $responseMock->method('getStatusCode')->willReturn(200);
        $responseMock->method('toArray')->willReturn(['name' => 'John Doe', 'email' => 'john@example.com']);

        $this->httpClientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://example.com/post-endpoint', ['json' => $payload])
            ->willReturn($responseMock);

        $this->loggerMock->expects($this->once())->method('info');

        $result = $this->apiService->randomPostRequest($payload);

        $this->assertEquals(['name' => 'John Doe', 'email' => 'john@example.com'], $result);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function testRandomPostRequestRetriesOnFailure(): void
    {
        $payload = ['key1' => 'value1', 'key2' => 'value2'];
        $responseMock = $this->createMock(ResponseInterface::class);

        // Simulate a failure response on the first attempt
        $responseMock->method('getStatusCode')->willReturn(500);
        $responseMock->method('toArray')->willReturn([]);

        $this->httpClientMock
            ->expects($this->exactly(2))
            ->method('request')
            ->with('POST', 'https://example.com/post-endpoint', ['json' => $payload])
            ->willReturn($responseMock);

        $this->loggerMock->expects($this->exactly(2))->method('warning');
        $this->loggerMock->expects($this->once())->method('error');

        $this->expectException(\Exception::class);
        $this->apiService->randomPostRequest($payload);
    }

    /**
     * @throws DecodingExceptionInterface
     */
    public function testRandomPostRequestHandlesTransportException(): void
    {
        $payload = ['key1' => 'value1', 'key2' => 'value2'];

        // Simulate a transport exception
        $this->httpClientMock
            ->method('request')
            ->willThrowException($this->createMock(TransportExceptionInterface::class));

        $this->loggerMock->expects($this->once())->method('error');

        $this->expectException(TransportExceptionInterface::class);
        $this->apiService->randomPostRequest($payload);
    }
}
