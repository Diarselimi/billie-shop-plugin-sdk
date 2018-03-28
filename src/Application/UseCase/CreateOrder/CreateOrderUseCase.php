<?php

namespace App\Application\UseCase\CreateOrder;

use App\DomainModel\Order\OrderRepositoryInterface;

class CreateOrderUseCase
{
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function execute(CreateOrderRequest $request)
    {
        $this->orderRepository->insert($request->getOrderData());
    }
}
