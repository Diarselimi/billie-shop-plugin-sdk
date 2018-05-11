<?php

namespace App\Http\Controller;

use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\Application\UseCase\UpdateOrder\UpdateOrderUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateOrderController
{
    private $useCase;

    public function __construct(UpdateOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $externalCode, Request $request): JsonResponse
    {
        $orderRequest = (new UpdateOrderRequest($externalCode))
            ->setCustomerId($request->headers->get(HttpConstantsInterface::REQUEST_HEADER_API_USER))
            ->setAmountGross($request->request->get('amount_gross'))
            ->setAmountNet($request->request->get('amount_net'))
            ->setAmountTax($request->request->get('amount_tax'))
            ->setDuration($request->request->get('duration'));

        $this->useCase->execute($orderRequest);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
