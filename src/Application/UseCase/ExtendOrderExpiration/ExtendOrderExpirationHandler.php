<?php

namespace App\Application\UseCase\ExtendOrderExpiration;

use App\Application\CommandHandler;
use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;

class ExtendOrderExpirationHandler implements CommandHandler
{
    private OrderRepositoryInterface $repository;

    public function __construct(OrderRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(ExtendOrderExpiration $command): void
    {
        $order = $this->loadOrder($command);

        $order->extendExpiration($command->newExpiration());
        $this->repository->update($order);
    }

    private function loadOrder(ExtendOrderExpiration $command): OrderEntity
    {
        $order = $this->repository->getOneByUuid($command->oderUuid());

        if (null === $order) {
            throw new OrderNotFoundException();
        }

        return $order;
    }
}
