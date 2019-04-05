<?php

namespace App\Http\Controller;

use App\Application\Exception\FraudOrderException;
use App\Application\UseCase\ConfirmOrderPayment\ConfirmOrderPaymentRequest;
use App\Application\UseCase\ConfirmOrderPayment\ConfirmOrderPaymentUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ConfirmOrderPaymentController
{
    private $useCase;

    public function __construct(ConfirmOrderPaymentUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $id, Request $request): void
    {
        try {
            $orderRequest = new ConfirmOrderPaymentRequest(
                $id,
                $request->headers->get(HttpConstantsInterface::REQUEST_HEADER_API_USER),
                $request->request->get('amount')
            );
            $this->useCase->execute($orderRequest);
        } catch (FraudOrderException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        }
    }
}
