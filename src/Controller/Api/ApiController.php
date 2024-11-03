<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Domain\Api\Request\ApiRequest;
use App\Domain\Api\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ApiController extends AbstractController
{
    private ApiService $apiService;

    public function __construct(
        ApiService $apiService
    ) {
        $this->apiService = $apiService;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     */
    #[Route('/send', name: 'send', methods: ['POST'])]
    public function index(
        #[MapRequestPayload] ApiRequest $request
    ): Response {
        $payload = [
            'name' => $request->name,
            'email' => $request->email
        ];
        $response = $this->apiService->randomPostRequest($payload);

        return $this->json($response);
    }
}
