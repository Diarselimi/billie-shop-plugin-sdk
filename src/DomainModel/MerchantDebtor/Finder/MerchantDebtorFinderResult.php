<?php

namespace App\DomainModel\MerchantDebtor\Finder;

use App\DomainModel\DebtorCompany\IdentifiedDebtorCompany;
use App\DomainModel\DebtorCompany\MostSimilarCandidateDTO;
use App\DomainModel\DebtorCompany\NullMostSimilarCandidateDTO;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;

class MerchantDebtorFinderResult
{
    private $merchantDebtor;

    private $identifiedDebtorCompany;

    private $mostSimilarCandidateDTO;

    public function __construct(
        MerchantDebtorEntity $merchantDebtor = null,
        IdentifiedDebtorCompany $identifiedDebtorCompany = null,
        MostSimilarCandidateDTO $mostSimilarCandidateDTO = null
    ) {
        $this->mostSimilarCandidateDTO = $mostSimilarCandidateDTO ?? new NullMostSimilarCandidateDTO();
        $this->merchantDebtor = $merchantDebtor;
        $this->identifiedDebtorCompany = $identifiedDebtorCompany;
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
}
