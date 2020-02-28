<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorCompany;

interface DebtorCompanyMatcherInterface
{
    public function matches(DebtorCompanyRequest $newCompanyData, DebtorCompany $existingCompany): bool;
}
