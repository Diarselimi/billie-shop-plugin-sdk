<?php

declare(strict_types=1);

namespace App\Infrastructure\Alfred\Dto;

use App\DomainModel\ArrayableInterface;

class StrictMatchRequestDTO implements ArrayableInterface
{
    private $company;

    private $companyToCompare;

    public function __construct(array $company, array $companyToCompare)
    {
        $this->company = $company;
        $this->companyToCompare = $companyToCompare;
    }

    public function toArray(): array
    {
        return [
            'company_to_compare' => $this->company,
            'company_to_compare_with' => $this->companyToCompare,
        ];
    }
}
