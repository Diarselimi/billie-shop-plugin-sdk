<?php

namespace App\Application\UseCase\ConfirmOrderPayment;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\PaymentOrderConfirmException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Payment\PaymentRequestFactory;
use App\DomainModel\Payment\PaymentsServiceInterface;
use Ozean12\Money\Money;

/**
 * @deprecated Use ConfirmInvoicePayment* classes
 */
class ConfirmOrderPaymentUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private OrderContainerFactory $orderContainerFactory;

    private PaymentsServiceInterface $paymentService;

    private PaymentRequestFactory $paymentRequestFactory;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        PaymentsServiceInterface $paymentService,
        PaymentRequestFactory $paymentRequestFactory
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->paymentService = $paymentService;
        $this->paymentRequestFactory = $paymentRequestFactory;
    }

    public function execute(ConfirmOrderPaymentRequest $request): void
    {
        $this->validateRequest($request);

        try {
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalIdOrUuid($request->getMerchantId(), $request->getOrderId());
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException();
        }

        $invoice = $orderContainer->getInvoices()->getLastInvoice();
        if ($invoice === null) {
            throw new PaymentOrderConfirmException();
        }

        $requestDTO = $this->paymentRequestFactory->createConfirmRequestDTOFromInvoice(
            $invoice,
            new Money($request->getAmount()),
            $orderContainer->getOrder()->getExternalCode()
        );
        $this->paymentService->confirmPayment($requestDTO);
    }
}
