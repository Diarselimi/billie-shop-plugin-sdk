<?php

namespace App\Application\UseCase\GetOrder;

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

        return new GetOrderResponse($order);
    }
}
