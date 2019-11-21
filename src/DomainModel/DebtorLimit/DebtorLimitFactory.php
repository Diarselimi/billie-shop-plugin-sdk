<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorLimit;

class DebtorLimitFactory
{
    public function createFromLimesResponse(array $data): DebtorLimit
    {
        return (new DebtorLimit())
            ->setGlobalFinancingLimit($data['financing_limit'])
            ->setGlobalAvailableFinancingLimit($data['available_financing_limit'])
        ;
    }
}
