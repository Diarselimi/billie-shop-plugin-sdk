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

    public function execute(string $externalCode, Request $request): JsonResponse
    {
        $useCaseRequest = new CreateOrderInvoiceRequest(
            $request->headers->get(HttpConstantsInterface::REQUEST_HEADER_API_USER),
            $externalCode,
            $request->request->get('file_id'),
            $request->request->get('invoice_number')
        );

        $this->createOrderInvoiceUseCase->execute($useCaseRequest);

        return new JsonResponse(null, JsonResponse::HTTP_CREATED);
    }
}
