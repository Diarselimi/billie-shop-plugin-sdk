<?php

namespace App\DomainModel\Payment;

use App\DomainModel\Order\OrderEntity;

interface PaymentsServiceInterface
{
    public function registerDebtor(string $paymentMerchantId): DebtorPaymentRegistrationDTO;

    public function getDebtorPaymentDetails(string $debtorPaymentId): DebtorPaymentDetailsDTO;

    public function getOrderPaymentDetails(string $orderPaymentId): OrderPaymentDetailsDTO;

    public function cancelOrder(OrderEntity $order): void;

    public function modifyOrder(string $paymentId, int $duration, float $amountGross, ?string $invoiceNumber): void;

    public function confirmPayment(OrderEntity $order, float $amount): void;

    public function createOrder(
        string $debtorPaymentId,
        string $invoiceNumber,
        \DateTime $shippedAt,
        int $duration,
        float $amountGross,
        string $externalCode
    ): OrderPaymentDetailsDTO;

    public function createFraudReclaim(string $orderPaymentId): void;
}
