<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Domain\Api\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SendControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ApiService $apiServiceMock;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        // Mock the ApiService
        $this->apiServiceMock = $this->createMock(ApiService::class);

        // Replace the real ApiService with the mock in the container
        $container = static::getContainer();
        $container->set(ApiService::class, $this->apiServiceMock);
    }

    public function testIndexSuccessfulRequest(): void
    {
        $payload = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];
        $apiResponse = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];

        $this->apiServiceMock
            ->expects($this->once())
            ->method('randomPostRequest')
            ->with($payload)
            ->willReturn($apiResponse);

        $this->client->request('POST', '/send', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertSame($apiResponse, json_decode($response->getContent(), true));
    }

    public function testIndexBadRequest(): void
    {
        $payload = [
            'name' => 'John Doe'
            // Missing 'email'
        ];

        $this->client->request('POST', '/send', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testIndexApiServiceThrowsException(): void
    {
        $payload = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];

        $this->apiServiceMock
            ->method('randomPostRequest')
            ->with($payload)
            ->willThrowException(new \Exception('API error'));

        $this->client->request('POST', '/send', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertStringContainsString('API error', $response->getContent());
    }

}
