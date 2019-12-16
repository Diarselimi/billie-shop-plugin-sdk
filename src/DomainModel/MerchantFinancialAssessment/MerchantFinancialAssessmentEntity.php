<?php

declare(strict_types=1);

namespace App\DomainModel\MerchantFinancialAssessment;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class MerchantFinancialAssessmentEntity extends AbstractTimestampableEntity
{
    private $data;

    private $merchantId;

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): MerchantFinancialAssessmentEntity
    {
        $this->data = $data;

        return $this;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): MerchantFinancialAssessmentEntity
    {
        $this->merchantId = $merchantId;

        return $this;
    }
}
