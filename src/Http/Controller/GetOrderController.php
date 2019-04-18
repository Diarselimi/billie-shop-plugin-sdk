<?php

namespace App\Http\Controller;

use App\Application\UseCase\GetOrder\GetOrderRequest;
use App\Application\UseCase\GetOrder\GetOrderUseCase;
use App\Application\UseCase\Response\OrderResponse;
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
        $response = $this->useCase->execute($request);

        return $response;
    }
}
