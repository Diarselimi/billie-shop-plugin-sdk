<?php

namespace App\DomainModel\DebtorCompany;

use App\DomainModel\AbstractServiceRequestException;

class CompaniesServiceRequestException extends AbstractServiceRequestException
{
    public function getServiceName(): string
    {
        return 'companies';
    }
}
