<?php

namespace App\DomainModel\DebtorExternalData;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;

class DebtorExternalDataEntityFactory
{
    public function createFromRequest(CreateOrderRequest $request): DebtorExternalDataEntity
    {
        return (new DebtorExternalDataEntity())
            ->setName($request->getDebtorCompanyName())
            ->setTaxId($request->getDebtorCompanyTaxId())
            ->setTaxNumber($request->getDebtorCompanyTaxNumber())
            ->setRegistrationCourt($request->getDebtorCompanyRegistrationCourt())
            ->setRegistrationNumber($request->getDebtorCompanyRegistrationNumber())
            ->setLegalForm($request->getDebtorCompanyLegalForm())
            ->setIndustrySector($request->getDebtorCompanyIndustrySector())
            ->setSubindustrySector($request->getDebtorCompanySubindustrySector())
            ->setEmployeesNumber($request->getDebtorCompanyEmployeesNumber())
            ->setEstablishedCustomer($request->getDebtorCompanyEstablishedCustomer())
            ->setMerchantExternalId($request->getMerchantCustomerId())
        ;
    }

    public function createFromDatabaseRow(array $row): DebtorExternalDataEntity
    {
        return (new DebtorExternalDataEntity())
            ->setId($row['id'])
            ->setName($row['name'])
            ->setTaxId($row['tax_id'])
            ->setTaxNumber($row['tax_number'])
            ->setRegistrationNumber($row['registration_number'])
            ->setRegistrationCourt($row['registration_court'])
            ->setIndustrySector($row['industry_sector'])
            ->setSubindustrySector($row['subindustry_sector'])
            ->setEmployeesNumber($row['employees_number'])
            ->setLegalForm($row['legal_form'])
            ->setEstablishedCustomer($row['is_established_customer'])
            ->setAddressId($row['address_id'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
