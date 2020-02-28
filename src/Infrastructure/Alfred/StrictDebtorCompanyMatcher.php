<?php

declare(strict_types=1);

namespace App\Infrastructure\Alfred;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorCompany\DebtorCompanyMatcherInterface;
use App\DomainModel\DebtorCompany\DebtorCompanyRequest;
use App\DomainModel\DebtorCompany\IdentifyDebtorRequestDTO;

class StrictDebtorCompanyMatcher implements DebtorCompanyMatcherInterface
{
    private $companiesService;

    public function __construct(CompaniesServiceInterface $companiesService)
    {
        $this->companiesService = $companiesService;
    }

    public function matches(DebtorCompanyRequest $newCompanyData, DebtorCompany $existingCompany): bool
    {
        $request = (new IdentifyDebtorRequestDTO())
            ->setName($newCompanyData->getName())
            ->setStreet($newCompanyData->getAddressStreet())
            ->setHouseNumber($newCompanyData->getAddressHouseNumber())
            ->setCity($newCompanyData->getAddressCity())
            ->setPostalCode($newCompanyData->getAddressPostalCode())
            ->setCountry($newCompanyData->getAddressCountry())
            ->setCompanyId($existingCompany->getId())
            ->setCompanyUuid($existingCompany->getUuid())
            ->setIsExperimental(false)
        ;

        return $this->companiesService->strictMatchDebtor($existingCompany->getUuid(), $request);
    }
}
