<?php

declare(strict_types=1);

namespace App\DomainModel\MerchantFinancialAssessment;

use App\Support\AbstractFactory;

class MerchantFinancialAssessmentEntityFactory extends AbstractFactory
{
    public function createFromArray(array $row): MerchantFinancialAssessmentEntity
    {
        return (new MerchantFinancialAssessmentEntity())
            ->setData(json_decode($row['data'], true))
            ->setMerchantId((int) $row['merchant_id']);
    }

    public function createFromDataAndMerchant(array $data, int $merchantId): MerchantFinancialAssessmentEntity
    {
        return
            (new MerchantFinancialAssessmentEntity())
            ->setData($data)
            ->setMerchantId($merchantId);
    }
}
