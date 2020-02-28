<?php

declare(strict_types=1);

namespace App\DomainModel\CheckoutSession;

// TODO: APIS-1946 // refactor all amount-related code into App\DomainModel\Amount and stop using App\Application layer here
use App\Application\UseCase\CreateOrder\Request\CreateOrderAmountRequest;
use App\DomainModel\DebtorCompany\DebtorCompanyRequest;

class CheckoutOrderRequestDTO
{
    private $sessionUuid;

    private $amount;

    private $duration;

    private $debtorCompany;

    public function getAmount(): CreateOrderAmountRequest
    {
        return $this->amount;
    }

    public function setAmount(CreateOrderAmountRequest $amount): CheckoutOrderRequestDTO
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
