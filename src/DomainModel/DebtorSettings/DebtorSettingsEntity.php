<?php

namespace App\DomainModel\DebtorSettings;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class DebtorSettingsEntity extends AbstractTimestampableEntity
{
    private $companyUuid;

    private $isWhitelisted;

    public function getCompanyUuid(): string
    {
        return $this->companyUuid;
    }

    public function setCompanyUuid(string $companyUuid): DebtorSettingsEntity
    {
        $this->companyUuid = $companyUuid;

        return $this;
    }

    public function isWhitelisted(): bool
    {
        return $this->isWhitelisted;
    }

    public function setIsWhitelisted(bool $isWhitelisted): DebtorSettingsEntity
    {
        $this->isWhitelisted = $isWhitelisted;

        return $this;
    }
}
