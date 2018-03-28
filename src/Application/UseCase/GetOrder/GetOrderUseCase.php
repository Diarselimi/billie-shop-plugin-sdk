<?php

namespace App\Application\UseCase\GetOrder;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Order\OrderRepositoryInterface;

class GetOrderUseCase
{
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function execute(GetOrderRequest $request)
    {
        $externalCode = $request->getExternalCode();
        $order = $this->orderRepository->getOneByExternalCodeRaw($externalCode);

        if (!$order) {
            throw new PaellaCoreCriticalException(
                "Order #$externalCode not found",
                PaellaCoreCriticalException::CODE_NOT_FOUND
            );
        }

        return new GetOrderResponse($order);
    }
}
