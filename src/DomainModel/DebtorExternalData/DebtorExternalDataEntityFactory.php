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
}
