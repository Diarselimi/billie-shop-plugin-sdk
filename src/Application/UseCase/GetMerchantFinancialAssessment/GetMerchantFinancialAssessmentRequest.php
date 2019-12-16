<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetMerchantFinancialAssessment;

use App\Application\UseCase\ValidatedRequestInterface;

class GetMerchantFinancialAssessmentRequest implements ValidatedRequestInterface
{
    private $merchantId;

    public function __construct(int $merchantId)
    {
        $this->merchantId = $merchantId;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }
}
