<?php

declare(strict_types=1);

namespace App\DomainModel\CheckoutSession;

use App\Application\UseCase\CheckoutConfirmOrder\CheckoutConfirmDebtorCompanyRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use Ozean12\Money\TaxedMoney\TaxedMoney;

class CheckoutOrderRequestDTO
{
    private $sessionUuid;

    private $amount;

    private $duration;

    private $debtorCompany;

    private $deliveryAddress;

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

    public function getDebtorCompany(): CheckoutConfirmDebtorCompanyRequest
    {
        return $this->debtorCompany;
    }

    public function setDebtorCompany(CheckoutConfirmDebtorCompanyRequest $debtorCompany): CheckoutOrderRequestDTO
    {
        $this->debtorCompany = $debtorCompany;

        return $this;
    }

    public function getDeliveryAddress(): ?CreateOrderAddressRequest
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?CreateOrderAddressRequest $deliveryAddress): CheckoutOrderRequestDTO
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }
}
