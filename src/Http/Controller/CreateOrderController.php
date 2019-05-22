<?php

namespace App\Http\Controller;

use App\Application\UseCase\CreateOrder\CreateOrderUseCase;
use App\Http\RequestHandler\CreateOrderRequestFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CreateOrderController
{
    private $createOrderUseCase;

    private $orderRequestFactory;

    public function __construct(
        CreateOrderUseCase $createOrderUseCase,
        CreateOrderRequestFactory $orderRequestFactory
    ) {
        $this->createOrderUseCase = $createOrderUseCase;
        $this->orderRequestFactory = $orderRequestFactory;
    }

    public function execute(Request $request): JsonResponse
    {
        $useCaseRequest = $this->orderRequestFactory
            ->createForCreateOrder($request);

        $response = $this->createOrderUseCase->execute($useCaseRequest);

        return new JsonResponse($response->toArray(), JsonResponse::HTTP_CREATED);
    }
}
