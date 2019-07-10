<?php

namespace App\DomainModel\Payment;

class DebtorPaymentRegistrationDTO
{
    private $paymentDebtorId;

    public function __construct(string $paymentDebtorId)
    {
        $this->paymentDebtorId = $paymentDebtorId;
    }

    public function getPaymentDebtorId(): string
    {
        return $this->paymentDebtorId;
    }

    public function setPaymentDebtorId(string $paymentDebtorId): DebtorPaymentRegistrationDTO
    {
        $this->paymentDebtorId = $paymentDebtorId;

        return $this;
    }
}
