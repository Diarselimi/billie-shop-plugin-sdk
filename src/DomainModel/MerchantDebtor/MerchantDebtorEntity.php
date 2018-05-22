<?php

namespace App\DomainModel\MerchantDebtor;

use App\DomainModel\AbstractEntity;

class MerchantDebtorEntity extends AbstractEntity
{
    private $merchantId;
    private $debtorId;
    private $externalId;

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function setMerchantId(string $merchantId): MerchantDebtorEntity
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getDebtorId(): string
    {
        return $this->debtorId;
    }

    public function setDebtorId(string $debtorId): MerchantDebtorEntity
    {
        $this->debtorId = $debtorId;

        return $this;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): MerchantDebtorEntity
    {
        $this->externalId = $externalId;

        return $this;
    }
}
