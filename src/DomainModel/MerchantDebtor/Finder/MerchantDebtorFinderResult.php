<?php

namespace App\DomainModel\MerchantDebtor\Finder;

use App\DomainModel\DebtorCompany\IdentifiedDebtorCompany;
use App\DomainModel\DebtorCompany\MostSimilarCandidateDTO;
use App\DomainModel\DebtorCompany\NullMostSimilarCandidateDTO;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;

class MerchantDebtorFinderResult
{
    private ?MerchantDebtorEntity $merchantDebtor;

    private ?IdentifiedDebtorCompany $identifiedDebtorCompany;

    private $mostSimilarCandidateDTO;

    private bool $allPreviousOrdersDeclined;

    public function __construct(
        MerchantDebtorEntity $merchantDebtor = null,
        IdentifiedDebtorCompany $identifiedDebtorCompany = null,
        MostSimilarCandidateDTO $mostSimilarCandidateDTO = null,
        bool $allPreviousOrdersDeclined = false
    ) {
        $this->mostSimilarCandidateDTO = $mostSimilarCandidateDTO ?? new NullMostSimilarCandidateDTO();
        $this->merchantDebtor = $merchantDebtor;
        $this->identifiedDebtorCompany = $identifiedDebtorCompany;
        $this->allPreviousOrdersDeclined = $allPreviousOrdersDeclined;
    }

    public function getMostSimilarCandidateDTO(): MostSimilarCandidateDTO
    {
        return $this->mostSimilarCandidateDTO;
    }

    public function getMerchantDebtor(): ?MerchantDebtorEntity
    {
        return $this->merchantDebtor;
    }

    public function getIdentifiedDebtorCompany(): ?IdentifiedDebtorCompany
    {
        return $this->identifiedDebtorCompany;
    }

    public function isAllPreviousOrdersDeclined(): bool
    {
        return $this->allPreviousOrdersDeclined;
    }
}
