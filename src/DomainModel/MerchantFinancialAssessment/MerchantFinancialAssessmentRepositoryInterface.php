<?php

declare(strict_types=1);

namespace App\DomainModel\MerchantFinancialAssessment;

interface MerchantFinancialAssessmentRepositoryInterface
{
    public function insert(MerchantFinancialAssessmentEntity $entity): void;

    public function findOneByMerchant(int $merchantId): ?MerchantFinancialAssessmentEntity;
}
