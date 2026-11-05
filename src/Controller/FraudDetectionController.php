<?php

declare(strict_types=1);

namespace App\Controller;

use App\Handler\FraudDetectionHandler;
use App\Model\FraudDetectionRequest;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Fraud Detection')]
final class FraudDetectionController extends AbstractController
{
    public function __construct(private readonly FraudDetectionHandler $fraudDetectionHandler)
    {
    }

    #[OA\Post(description: 'Receives a list of orders and returns the ids of the ones detected as fraudulent.', summary: 'Detect fraudulent orders')]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['orders'],
            properties: [
                new OA\Property(
                    property: 'orders',
                    type: 'array',
                    items: new OA\Items(
                        required: ['orderId', 'dealId', 'email', 'streetAddress', 'city', 'state', 'zipCode', 'creditCard'],
                        properties: [
                            new OA\Property(property: 'orderId', type: 'integer', example: 1),
                            new OA\Property(property: 'dealId', type: 'integer', example: 1),
                            new OA\Property(property: 'email', type: 'string', example: 'bugs@bunny.com'),
                            new OA\Property(property: 'streetAddress', type: 'string', example: '123 Sesame St.'),
                            new OA\Property(property: 'city', type: 'string', example: 'New York'),
                            new OA\Property(property: 'state', type: 'string', example: 'NY'),
                            new OA\Property(property: 'zipCode', type: 'string', example: '10011'),
                            new OA\Property(property: 'creditCard', type: 'string', example: '12345689010'),
                        ],
                        type: 'object',
                    ),
                ),
            ],
            type: 'object',
        ),
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Ids of the fraudulent orders, in the order they were received.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'fraudulentOrders', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2]),
            ],
            type: 'object',
        ),
    )]
    #[OA\Response(response: Response::HTTP_BAD_REQUEST, description: 'The body is not valid JSON.', content: new OA\JsonContent(ref: '#/components/schemas/error'))]
    #[OA\Response(response: Response::HTTP_UNPROCESSABLE_ENTITY, description: 'The "orders" array is missing or an order is invalid.', content: new OA\JsonContent(ref: '#/components/schemas/error'))]
    #[Route('/api/fraud-detection', name: 'api_fraud_detection', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] FraudDetectionRequest $request): JsonResponse
    {
        return $this->json(['fraudulentOrders' => $this->fraudDetectionHandler->handle($request->orders)]);
    }
}
