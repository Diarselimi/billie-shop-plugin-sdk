<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorLimit;

class DebtorLimitFactory
{
    public function createFromLimesResponse(array $response): DebtorLimitDTO
    {
        return (new DebtorLimitDTO())
            ->setGlobalFinancingLimit($response['financing_limit'])
            ->setGlobalAvailableFinancingLimit($response['available_financing_limit'])
            ->setDebtorCustomerLimits(array_map([$this, 'createDebtorCustomerLimitFromLimesResponse'], $response['debtor_customer_limits']))
        ;
    }

    private function createDebtorCustomerLimitFromLimesResponse(array $response): DebtorCustomerLimitDTO
    {
        return (new DebtorCustomerLimitDTO())
            ->setCustomerCompanyUuid($response['customer_company_uuid'])
            ->setFinancingLimit($response['financing_limit'])
            ->setAvailableFinancingLimit($response['available_financing_limit'])
        ;
    }
}
