<?php

namespace App\DomainModel\DebtorExternalData;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;

class DebtorExternalDataEntityFactory
{
    public function createFromRequest(CreateOrderRequest $request): DebtorExternalDataEntity
    {
        return (new DebtorExternalDataEntity())
            ->setName($request->getDebtorCompany()->getName())
            ->setTaxId($request->getDebtorCompany()->getTaxId())
            ->setTaxNumber($request->getDebtorCompany()->getTaxNumber())
            ->setRegistrationCourt($request->getDebtorCompany()->getRegistrationCourt())
            ->setRegistrationNumber($request->getDebtorCompany()->getRegistrationNumber())
            ->setLegalForm($request->getDebtorCompany()->getLegalForm())
            ->setIndustrySector($request->getDebtorCompany()->getIndustrySector())
            ->setSubindustrySector($request->getDebtorCompany()->getSubindustrySector())
            ->setEmployeesNumber($request->getDebtorCompany()->getEmployeesNumber())
            ->setEstablishedCustomer($request->getDebtorCompany()->isEstablishedCustomer())
            ->setMerchantExternalId($request->getDebtorCompany()->getMerchantCustomerId())
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
            ->setEstablishedCustomer(boolval($row['is_established_customer']))
            ->setAddressId($row['address_id'])
            ->setMerchantExternalId($row['merchant_external_id'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
            ->setDataHash($row['debtor_data_hash'])
        ;
    }
}
