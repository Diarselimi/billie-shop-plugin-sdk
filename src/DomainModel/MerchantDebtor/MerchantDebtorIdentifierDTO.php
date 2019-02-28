<?php

namespace App\DomainModel\MerchantDebtor;

class MerchantDebtorIdentifierDTO
{
    private $merchantDebtorId;

    private $merchantExternalId;

    private $merchantId;

    private $debtorId;

    public function getMerchantDebtorId(): int
    {
        return $this->merchantDebtorId;
    }

    public function setMerchantDebtorId(int $merchantDebtorId): MerchantDebtorIdentifierDTO
    {
        $this->merchantDebtorId = $merchantDebtorId;

        return $this;
    }

    public function getMerchantExternalId(): string
    {
        return $this->merchantExternalId;
    }

    public function setMerchantExternalId(string $merchantExternalId): MerchantDebtorIdentifierDTO
    {
        $this->merchantExternalId = $merchantExternalId;

        return $this;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): MerchantDebtorIdentifierDTO
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getDebtorId(): int
    {
        return $this->debtorId;
    }

    public function setDebtorId(int $debtorId): MerchantDebtorIdentifierDTO
    {
        $this->debtorId = $debtorId;

        return $this;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
