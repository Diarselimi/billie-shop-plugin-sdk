<?php

namespace App\Application\UseCase\IdentifyAndScoreDebtor;

class IdentifyAndScoreDebtorResponse
{
    private $companyId;

    private $isEligible;

    public function __construct(int $companyId, ?bool $isEligible)
    {
        $this->companyId = $companyId;
        $this->isEligible = $isEligible;
    }

    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    public function isEligible(): ? bool
    {
        return $this->isEligible;
    }
}
