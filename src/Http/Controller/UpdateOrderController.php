<?php

namespace App\Http\Controller;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\PaellaCoreCriticalException;
use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\Application\UseCase\UpdateOrder\UpdateOrderUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
                ->setMerchantId($request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID))
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
        } catch (OrderNotFoundException $exception) {
            throw new NotFoundHttpException();
        }
    }
}
