<?php

namespace App\Http\Controller;

use App\Application\UseCase\ConfirmOrderPayment\ConfirmOrderPaymentRequest;
use App\Application\UseCase\ConfirmOrderPayment\ConfirmOrderPaymentUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfirmOrderPaymentController
{
    private $useCase;

    public function __construct(ConfirmOrderPaymentUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $externalCode, Request $request): JsonResponse
    {
        $orderRequest = new ConfirmOrderPaymentRequest(
            $externalCode,
            $request->headers->get(HttpConstantsInterface::REQUEST_HEADER_API_USER),
            $request->request->get('amount')
        );
        $this->useCase->execute($orderRequest);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
