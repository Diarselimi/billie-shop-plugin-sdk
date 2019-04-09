<?php

namespace App\Http\Controller;

use App\Application\UseCase\GetOrder\GetOrderRequest;
use App\Application\UseCase\GetOrder\GetOrderResponse;
use App\Application\UseCase\GetOrder\GetOrderUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;

class GetOrderController
{
    private $useCase;

    public function __construct(GetOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $id, Request $request): GetOrderResponse
    {
        $request = new GetOrderRequest($id, $request->headers->get(HttpConstantsInterface::REQUEST_HEADER_API_USER));
        $response = $this->useCase->execute($request);

        return $response;
    }
}
