<?php

namespace App\DomainModel\Payment;

use App\DomainModel\MerchantDebtor\RegisterDebtorDTO;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Payment\RequestDTO\ConfirmRequestDTO;
use App\DomainModel\Payment\RequestDTO\ModifyRequestDTO;

interface PaymentsServiceInterface
{
    public function registerDebtor(RegisterDebtorDTO $registerDebtorDTO): DebtorPaymentRegistrationDTO;

    public function getDebtorPaymentDetails(string $debtorPaymentId): DebtorPaymentDetailsDTO;

    public function getOrderPaymentDetails(string $orderPaymentId): OrderPaymentDetailsDTO;

    public function getBatchOrderPaymentDetails(array $orderPaymentIds): array;

    public function cancelOrder(OrderEntity $order): void;

    public function modifyOrder(ModifyRequestDTO $requestDTO): void;

    public function confirmPayment(ConfirmRequestDTO $requestDTO): void;

    public function createFraudReclaim(string $orderPaymentId): void;
}
