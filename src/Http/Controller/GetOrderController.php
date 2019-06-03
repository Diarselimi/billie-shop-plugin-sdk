<?php

namespace App\Http\Controller;

use App\Application\UseCase\GetOrder\GetOrderRequest;
use App\Application\UseCase\GetOrder\GetOrderUseCase;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;

class GetOrderController
{
    private $useCase;

    public function __construct(GetOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $id, Request $request): OrderResponse
    {
        $request = new GetOrderRequest($id, $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID));

        return $this->useCase->execute($request);
    }
}
