<?php

namespace App\DomainModel\FraudRules;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class FraudRuleEntity extends AbstractTimestampableEntity
{
    private $includedWords;

    private $excludedWords;

    private $checkForPublicDomain;

    public function getIncludedWords(): array
    {
        return $this->includedWords;
    }

    public function setIncludedWords(array $includedWords): FraudRuleEntity
    {
        $this->includedWords = $includedWords;

        return $this;
    }

    public function getExcludedWords(): array
    {
        return $this->excludedWords;
    }

    public function setExcludedWords(array $excludedWords): FraudRuleEntity
    {
        $this->excludedWords = $excludedWords;

        return $this;
    }

    public function isCheckForPublicDomainEnabled(): bool
    {
        return $this->checkForPublicDomain;
    }

    public function setCheckForPublicDomain(bool $checkForPublicDomain): FraudRuleEntity
    {
        $this->checkForPublicDomain = $checkForPublicDomain;

        return $this;
    }
}
