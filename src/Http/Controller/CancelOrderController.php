<?php

namespace App\Http\Controller;

use App\Application\Exception\FraudOrderException;
use App\Application\UseCase\CancelOrder\CancelOrderRequest;
use App\Application\UseCase\CancelOrder\CancelOrderUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CancelOrderController
{
    private $useCase;

    public function __construct(CancelOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $id, Request $request): void
    {
        try {
            $orderRequest = new CancelOrderRequest(
                $id,
                $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID)
            );
            $this->useCase->execute($orderRequest);
        } catch (FraudOrderException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        }
    }
}
