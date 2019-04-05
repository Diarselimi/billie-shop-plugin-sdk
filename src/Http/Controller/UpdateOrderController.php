<?php

namespace App\Http\Controller;

use App\Application\Exception\FraudOrderException;
use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\Application\UseCase\UpdateOrder\UpdateOrderUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UpdateOrderController
{
    private $useCase;

    public function __construct(UpdateOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $id, Request $request): void
    {
        try {
            $orderRequest = (new UpdateOrderRequest($id))
                ->setMerchantId($request->headers->get(HttpConstantsInterface::REQUEST_HEADER_API_USER))
                ->setAmountGross($request->request->get('amount_gross'))
                ->setAmountNet($request->request->get('amount_net'))
                ->setAmountTax($request->request->get('amount_tax'))
                ->setDuration($request->request->get('duration'))
                ->setInvoiceNumber($request->request->get('invoice_number'))
                ->setInvoiceUrl($request->request->get('invoice_url'))
            ;

            $this->useCase->execute($orderRequest);
        } catch (FraudOrderException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        }
    }
}
