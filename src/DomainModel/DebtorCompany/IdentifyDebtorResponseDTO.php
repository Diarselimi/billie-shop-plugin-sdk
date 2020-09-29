<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorCompany;

class IdentifyDebtorResponseDTO
{
    private $identifiedDebtorCompany;

    private $mostSimilarCandidate;

    public function __construct(
        ?IdentifiedDebtorCompany $identifiedDebtorCompany,
        MostSimilarCandidateDTO $mostSimilarCandidate
    ) {
        $this->identifiedDebtorCompany = $identifiedDebtorCompany;
        $this->mostSimilarCandidate = $mostSimilarCandidate;
    }

    public function getIdentifiedDebtorCompany(): ?IdentifiedDebtorCompany
    {
        return $this->identifiedDebtorCompany;
    }

    public function getMostSimilarCandidate(): MostSimilarCandidateDTO
    {
        return $this->mostSimilarCandidate;
    }
}
