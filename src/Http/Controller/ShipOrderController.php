<?php

namespace App\Http\Controller;

use App\Application\UseCase\ShipOrder\ShipOrderRequest;
use App\Application\UseCase\ShipOrder\ShipOrderUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;

class ShipOrderController
{
    private $useCase;

    public function __construct(ShipOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $id, Request $request): void
    {
        $orderRequest = (new ShipOrderRequest())
            ->setOrderId($id)
            ->setExternalCode($request->request->get('external_order_id'))
            ->setMerchantId($request->headers->get(HttpConstantsInterface::REQUEST_HEADER_API_USER))
            ->setInvoiceNumber($request->request->get('invoice_number'))
            ->setInvoiceUrl($request->request->get('invoice_url'))
            ->setProofOfDeliveryUrl($request->request->get('proof_of_delivery_url'))
        ;

        $this->useCase->execute($orderRequest);
    }
}
