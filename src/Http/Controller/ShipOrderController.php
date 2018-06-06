<?php

namespace App\Http\Controller;

use App\Application\UseCase\CancelOrder\CancelOrderRequest;
use App\Application\UseCase\CancelOrder\CancelOrderUseCase;
use App\Application\UseCase\ShipOrder\ShipOrderRequest;
use App\Application\UseCase\ShipOrder\ShipOrderUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ShipOrderController
{
    private $useCase;

    public function __construct(ShipOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $externalCode, Request $request): JsonResponse
    {
        $orderRequest = new ShipOrderRequest(
            $externalCode,
            $request->headers->get(HttpConstantsInterface::REQUEST_HEADER_API_USER)
        );
        $this->useCase->execute($orderRequest);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
