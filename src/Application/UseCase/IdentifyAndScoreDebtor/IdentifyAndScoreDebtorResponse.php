<?php

namespace App\Application\UseCase\IdentifyAndScoreDebtor;

class IdentifyAndScoreDebtorResponse
{
    private $companyId;

    private $companyName;

    private $crefoId;

    private $isEligible;

    private $isStrictMatch;

    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    public function setCompanyId(int $companyId): IdentifyAndScoreDebtorResponse
    {
        $this->companyId = $companyId;

        return $this;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): IdentifyAndScoreDebtorResponse
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getCrefoId(): ? string
    {
        return $this->crefoId;
    }

    public function setCrefoId(?string $crefoId): IdentifyAndScoreDebtorResponse
    {
        $this->crefoId = $crefoId;

        return $this;
    }

    public function setIsEligible(?bool $isEligible): IdentifyAndScoreDebtorResponse
    {
        $this->isEligible = $isEligible;

        return $this;
    }

    public function isEligible(): ? bool
    {
        return $this->isEligible;
    }

    public function isStrictMatch(): bool
    {
        return $this->isStrictMatch;
    }

    public function setIsStrictMatch(bool $isStrictMatch): IdentifyAndScoreDebtorResponse
    {
        $this->isStrictMatch = $isStrictMatch;

        return $this;
    }
}
