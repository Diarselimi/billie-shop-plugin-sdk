<?php

namespace App\DomainModel\DebtorCompany;

use App\DomainModel\Order\OrderContainer\OrderContainer;

class IdentifyDebtorRequestFactory
{
    public function createDebtorRequestDTO(OrderContainer $orderContainer): IdentifyDebtorRequestDTO
    {
        return (new IdentifyDebtorRequestDTO())
            ->setName($orderContainer->getDebtorExternalData()->getName())
            ->setHouseNumber($orderContainer->getDebtorExternalDataAddress()->getHouseNumber())
            ->setStreet($orderContainer->getDebtorExternalDataAddress()->getStreet())
            ->setPostalCode($orderContainer->getDebtorExternalDataAddress()->getPostalCode())
            ->setCity($orderContainer->getDebtorExternalDataAddress()->getCity())
            ->setCountry($orderContainer->getDebtorExternalDataAddress()->getCountry())
            ->setTaxId($orderContainer->getDebtorExternalData()->getTaxId())
            ->setTaxNumber($orderContainer->getDebtorExternalData()->getTaxNumber())
            ->setRegistrationNumber($orderContainer->getDebtorExternalData()->getRegistrationNumber())
            ->setRegistrationCourt($orderContainer->getDebtorExternalData()->getRegistrationCourt())
            ->setLegalForm($orderContainer->getDebtorExternalData()->getLegalForm())
            ->setFirstName($orderContainer->getDebtorPerson()->getFirstName())
            ->setLastName($orderContainer->getDebtorPerson()->getLastName())
            ->setIsExperimental($orderContainer->getMerchantSettings()->useExperimentalDebtorIdentification())
        ;
    }
}
