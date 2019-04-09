<?php

namespace App\Application\UseCase\GetOrder;

use App\Application\PaellaCoreCriticalException;
use App\Application\UseCase\Response\OrderResponse;
use App\Application\UseCase\Response\OrderResponseFactory;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

class GetOrderUseCase
{
    private $orderRepository;

    private $orderPersistenceService;

    private $orderResponseFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        OrderResponseFactory $orderResponseFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderPersistenceService = $orderPersistenceService;
        $this->orderResponseFactory = $orderResponseFactory;
    }

    public function execute(GetOrderRequest $request): OrderResponse
    {
        $order = $this->orderRepository->getOneByMerchantIdAndExternalCodeOrUUID($request->getOrderId(), $request->getMerchantId());

        if (!$order) {
            throw new PaellaCoreCriticalException(
                "Order #{$request->getOrderId()} not found",
                PaellaCoreCriticalException::CODE_NOT_FOUND,
                Response::HTTP_NOT_FOUND
            );
        }

        $orderContainer = $this->orderPersistenceService->createFromOrderEntity($order);

        return $this->orderResponseFactory->create($orderContainer);
    }
}
