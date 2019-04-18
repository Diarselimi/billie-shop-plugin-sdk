<?php

namespace App\Http\Controller;

use App\Application\UseCase\CreateOrderInvoice\CreateOrderInvoiceRequest;
use App\Application\UseCase\CreateOrderInvoice\CreateOrderInvoiceUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CreateOrderInvoiceController
{
    private $createOrderInvoiceUseCase;

    public function __construct(CreateOrderInvoiceUseCase $createOrderInvoiceUseCase)
    {
        $this->createOrderInvoiceUseCase = $createOrderInvoiceUseCase;
    }

    public function execute(string $id, Request $request): JsonResponse
    {
        $useCaseRequest = new CreateOrderInvoiceRequest(
            $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID),
            $id,
            $request->request->get('file_id'),
            $request->request->get('invoice_number')
        );

        $this->createOrderInvoiceUseCase->execute($useCaseRequest);

        return new JsonResponse(null, JsonResponse::HTTP_CREATED);
    }
}
