<?php

namespace App\DomainModel\DebtorExternalData;

use App\Application\UseCase\CreateOrder\CreateOrderRequestInterface;

class DebtorExternalDataEntityFactory
{
    public function createFromRequest(CreateOrderRequestInterface $request): DebtorExternalDataEntity
    {
        return (new DebtorExternalDataEntity())
            ->setName($request->getDebtor()->getName())
            ->setTaxId($request->getDebtor()->getTaxId())
            ->setTaxNumber($request->getDebtor()->getTaxNumber())
            ->setRegistrationCourt($request->getDebtor()->getRegistrationCourt())
            ->setRegistrationNumber($request->getDebtor()->getRegistrationNumber())
            ->setLegalForm($request->getDebtor()->getLegalForm())
            ->setIndustrySector($request->getDebtor()->getIndustrySector())
            ->setSubindustrySector($request->getDebtor()->getSubindustrySector())
            ->setEmployeesNumber($request->getDebtor()->getEmployeesNumber())
            ->setEstablishedCustomer($request->getDebtor()->isEstablishedCustomer())
            ->setMerchantExternalId($request->getDebtor()->getMerchantCustomerId());
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
            ->setBillingAddressId($row['billing_address_id'])
            ->setMerchantExternalId($row['merchant_external_id'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
            ->setDataHash($row['debtor_data_hash']);
    }
}
