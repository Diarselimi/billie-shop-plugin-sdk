<?php

namespace App\DomainModel\Borscht;

use App\DomainModel\Order\OrderEntity;

interface BorschtInterface
{
    public function registerDebtor(string $paymentMerchantId): DebtorPaymentRegistrationDTO;

    public function getDebtorPaymentDetails(string $debtorPaymentId): DebtorPaymentDetailsDTO;

    public function getOrderPaymentDetails(string $orderPaymentId): OrderPaymentDetailsDTO;

    public function cancelOrder(OrderEntity $order): void;

    public function modifyOrder(OrderEntity $order): void;

    public function confirmPayment(OrderEntity $order, float $amount): void;

    public function createOrder(OrderEntity $order, string $debtorPaymentId): OrderPaymentDetailsDTO;
}
