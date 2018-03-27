<?php

namespace App\Application\UseCase\GetOrder;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Order\OrderRepositoryInterface;

class GetOrderUseCase
{
    private $repository;

    public function __construct(OrderRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(GetOrderRequest $request)
    {
        $externalCode = $request->getExternalCode();
        $order = $this->repository->getOneByExternalCodeRaw($externalCode);

        if (!$order) {
            throw new PaellaCoreCriticalException(
                "Order #$externalCode not found",
                PaellaCoreCriticalException::CODE_NOT_FOUND
            );
        }

        return new GetOrderResponse($order);
    }
}
