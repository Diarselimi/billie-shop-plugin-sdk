<?php

declare(strict_types=1);

namespace App\DomainModel\CheckoutSession;

use Ozean12\Money\TaxedMoney\TaxedMoney;
use App\DomainModel\DebtorCompany\DebtorCompanyRequest;

class CheckoutOrderRequestDTO
{
    private $sessionUuid;

    private $amount;

    private $duration;

    private $debtorCompany;

    public function getAmount(): TaxedMoney
    {
        return $this->amount;
    }

    public function setAmount(TaxedMoney $amount): CheckoutOrderRequestDTO
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): CheckoutOrderRequestDTO
    {
        $this->duration = $duration;

        return $this;
    }

    public function getSessionUuid(): string
    {
        return $this->sessionUuid;
    }

    public function setSessionUuid(string $sessionUuid): CheckoutOrderRequestDTO
    {
        $this->sessionUuid = $sessionUuid;

        return $this;
    }

    public function getDebtorCompany(): DebtorCompanyRequest
    {
        return $this->debtorCompany;
    }

    public function setDebtorCompany(DebtorCompanyRequest $debtorCompany): CheckoutOrderRequestDTO
    {
        $this->debtorCompany = $debtorCompany;

        return $this;
    }
}
