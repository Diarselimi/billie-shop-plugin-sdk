<?php

namespace App\Application\UseCase\ConfirmOrderPayment;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\PaymentOrderConfirmException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Payment\PaymentRequestFactory;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Order\OrderRepositoryInterface;

/**
 * @deprecated Use ConfirmInvoicePayment* classes
 */
class ConfirmOrderPaymentUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private OrderRepositoryInterface $orderRepository;

    private PaymentsServiceInterface $paymentService;

    private PaymentRequestFactory $paymentRequestFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        PaymentsServiceInterface $paymentService,
        PaymentRequestFactory $paymentRequestFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->paymentService = $paymentService;
        $this->paymentRequestFactory = $paymentRequestFactory;
    }

    public function execute(ConfirmOrderPaymentRequest $request): void
    {
        $this->validateRequest($request);

        $order = $this->orderRepository->getOneByMerchantIdAndExternalCodeOrUUID($request->getOrderId(), $request->getMerchantId());

        if (!$order) {
            throw new OrderNotFoundException();
        }

        if ($order->wasShipped()) {
            $requestDTO = $this->paymentRequestFactory->createConfirmRequestDTO($order, $request->getAmount());

            $this->paymentService->confirmPayment($requestDTO);
        } else {
            throw new PaymentOrderConfirmException();
        }
    }
}
