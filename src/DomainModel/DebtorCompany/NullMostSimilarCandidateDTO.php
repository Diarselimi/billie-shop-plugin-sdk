<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorCompany;

class NullMostSimilarCandidateDTO extends MostSimilarCandidateDTO
{
    public function __construct()
    {
        parent::__construct(
            '',
            '',
            null,
            null,
            null,
            null,
            null,
            '',
            '',
            '',
            '',
            ''
        );
    }
}
