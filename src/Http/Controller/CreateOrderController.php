<?php

namespace App\Http\Controller;

use App\Application\UseCase\CreateOrder\CreateOrderUseCase;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use App\Http\RequestHandler\CreateOrderRequestFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CreateOrderController
{
    private $createOrderUseCase;

    private $orderRequestFactory;

    private $orderResponseFactory;

    public function __construct(
        CreateOrderUseCase $createOrderUseCase,
        CreateOrderRequestFactory $orderRequestFactory,
        OrderResponseFactory $orderResponseFactory
    ) {
        $this->createOrderUseCase = $createOrderUseCase;
        $this->orderRequestFactory = $orderRequestFactory;
        $this->orderResponseFactory = $orderResponseFactory;
    }

    public function execute(Request $request): JsonResponse
    {
        $useCaseRequest = $this->orderRequestFactory
            ->createForCreateOrder($request);

        $orderContainer = $this->createOrderUseCase->execute($useCaseRequest);

        return new JsonResponse($this->orderResponseFactory->create($orderContainer)->toArray(), JsonResponse::HTTP_CREATED);
    }
}
