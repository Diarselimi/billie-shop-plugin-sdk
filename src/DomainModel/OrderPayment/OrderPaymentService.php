<?php

declare(strict_types=1);

namespace App\DomainModel\OrderPayment;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Payment\OrderPaymentDetailsDTO;
use App\DomainModel\Payment\PaymentRequestFactory;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Payment\PaymentsServiceRequestException;

class OrderPaymentService
{
    private $paymentsService;

    private $paymentRequestFactory;

    public function __construct(
        PaymentsServiceInterface $paymentsService,
        PaymentRequestFactory $paymentRequestFactory
    ) {
        $this->paymentsService = $paymentsService;
        $this->paymentRequestFactory = $paymentRequestFactory;
    }

    public function findPaymentDetails(OrderEntity $order): ?OrderPaymentDetailsDTO
    {
        if (!$order->getPaymentId()) {
            return null;
        }

        try {
            $paymentDetails = $this->paymentsService->getOrderPaymentDetails($order->getPaymentId());
        } catch (PaymentsServiceRequestException $exception) {
            $paymentDetails = null;
        }

        return $paymentDetails;
    }

    public function createPaymentsTicket(OrderContainer $orderContainer): void
    {
        $paymentRequest = $this->paymentRequestFactory->createCreateRequestDTO($orderContainer);
        $paymentDetails = $this->paymentsService->createOrder($paymentRequest);

        $orderContainer->setPaymentDetails($paymentDetails);
    }
}
