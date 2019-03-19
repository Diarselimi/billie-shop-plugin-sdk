<?php

namespace App\DomainModel\OrderIdentification;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class OrderIdentificationEntity extends AbstractTimestampableEntity
{
    private $orderId;

    private $v1CompanyId;

    private $v2CompanyId;

    private $v2StrictMatch;

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): OrderIdentificationEntity
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getV1CompanyId(): ? int
    {
        return $this->v1CompanyId;
    }

    public function setV1CompanyId(?int $v1CompanyId): OrderIdentificationEntity
    {
        $this->v1CompanyId = $v1CompanyId;

        return $this;
    }

    public function getV2CompanyId(): ? int
    {
        return $this->v2CompanyId;
    }

    public function setV2CompanyId(?int $v2CompanyId): OrderIdentificationEntity
    {
        $this->v2CompanyId = $v2CompanyId;

        return $this;
    }

    public function isV2StrictMatch(): ? bool
    {
        return $this->v2StrictMatch;
    }

    public function setV2StrictMatch(bool $v2StrictMatch): OrderIdentificationEntity
    {
        $this->v2StrictMatch = $v2StrictMatch;

        return $this;
    }
}
