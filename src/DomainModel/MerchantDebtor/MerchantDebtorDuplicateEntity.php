<?php

namespace App\DomainModel\MerchantDebtor;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class MerchantDebtorDuplicateEntity extends AbstractTimestampableEntity
{
    private $mainMerchantDebtorId;

    private $duplicatedMerchantDebtorId;

    public function getMainMerchantDebtorId(): int
    {
        return $this->mainMerchantDebtorId;
    }

    public function getDuplicatedMerchantDebtorId(): int
    {
        return $this->duplicatedMerchantDebtorId;
    }

    public function setMainMerchantDebtorId(int $mainMerchantDebtorId): MerchantDebtorDuplicateEntity
    {
        $this->mainMerchantDebtorId = $mainMerchantDebtorId;

        return $this;
    }

    public function setDuplicatedMerchantDebtorId(int $duplicatedMerchantDebtorId): MerchantDebtorDuplicateEntity
    {
        $this->duplicatedMerchantDebtorId = $duplicatedMerchantDebtorId;

        return $this;
    }
}
