<?php

namespace App\DomainModel\Company;

class CompanyEntityFactory
{
    public function create(): CompanyEntity
    {
        return new CompanyEntity();
    }
}
