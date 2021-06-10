<?php

namespace App\DomainModel\Payment;

use App\DomainModel\MerchantDebtor\RegisterDebtorDTO;
use App\DomainModel\Payment\RequestDTO\ConfirmRequestDTO;

interface PaymentsServiceInterface
{
    public function registerDebtor(RegisterDebtorDTO $registerDebtorDTO): DebtorPaymentRegistrationDTO;

    public function getDebtorPaymentDetails(string $debtorPaymentId): DebtorPaymentDetailsDTO;

    public function confirmPayment(ConfirmRequestDTO $requestDTO): void;

    public function createFraudReclaim(string $orderPaymentId): void;
}
