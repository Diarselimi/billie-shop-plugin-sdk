<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetOrderCompact;

use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderNotFoundException;
use Ramsey\Uuid\UuidInterface;

class GetOrderCompactUseCase
{
    private OrderContainerFactory $orderManagerFactory;

    public function __construct(OrderContainerFactory $orderManagerFactory)
    {
        $this->orderManagerFactory = $orderManagerFactory;
    }

    public function execute(UuidInterface $orderUuid): GetOrderCompactResponse
    {
        try {
            $orderContainer = $this->orderManagerFactory->loadByUuid($orderUuid->toString());
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException();
        }

        return new GetOrderCompactResponse($orderContainer);
    }
}
