<?php

namespace App\DomainModel\Borscht;

class DebtorPaymentDetailsDTO
{
    private $bankAccountIban;

    private $bankAccountBic;

    private $outstandingAmount;

    public function getBankAccountIban(): string
    {
        return $this->bankAccountIban;
    }

    public function setBankAccountIban(string $bankAccountIban): DebtorPaymentDetailsDTO
    {
        $this->bankAccountIban = $bankAccountIban;

        return $this;
    }

    public function getBankAccountBic(): string
    {
        return $this->bankAccountBic;
    }

    public function setBankAccountBic(string $bankAccountBic): DebtorPaymentDetailsDTO
    {
        $this->bankAccountBic = $bankAccountBic;

        return $this;
    }

    public function getOutstandingAmount(): float
    {
        return $this->outstandingAmount;
    }

    public function setOutstandingAmount(float $outstandingAmount): DebtorPaymentDetailsDTO
    {
        $this->outstandingAmount = $outstandingAmount;

        return $this;
    }
}
