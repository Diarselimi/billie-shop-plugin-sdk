<?php

namespace App\Http\Controller;

use App\Application\UseCase\GetOrder\GetOrderRequest;
use App\Application\UseCase\GetOrder\GetOrderUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetOrderController
{
    private $useCase;

    public function __construct(GetOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $externalCode)
    {
        $request = new GetOrderRequest($externalCode);
        $response = $this->useCase->execute($request);

        return new JsonResponse($response->getOrderData());
    }
}
