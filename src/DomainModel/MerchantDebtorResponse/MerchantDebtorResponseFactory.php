<?php

namespace App\DomainModel\MerchantDebtorResponse;

use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantDebtor\MerchantDebtorFinancialDetailsEntity;

class MerchantDebtorResponseFactory
{
    public function createFromContainer(MerchantDebtorContainer $container): MerchantDebtor
    {
        return (new MerchantDebtor())
            ->setUuid($container->getMerchantDebtor()->getUuid())
            ->setExternalCode($container->getExternalId())
            ->setName($container->getDebtorCompany()->getName())
            ->setAddressStreet($container->getDebtorCompany()->getAddressStreet())
            ->setAddressHouse($container->getDebtorCompany()->getAddressHouse())
            ->setAddressPostalCode($container->getDebtorCompany()->getAddressPostalCode())
            ->setAddressCity($container->getDebtorCompany()->getAddressCity())
            ->setAddressCountry($container->getDebtorCompany()->getAddressCountry())
            ->setFinancingLimit($container->getFinancialDetails()->getFinancingLimit())
            ->setFinancingPower($this->calculateFinancingPower($container->getDebtorCompany(), $container->getFinancialDetails()))
            ->setOutstandingAmount($container->getPaymentDetails()->getOutstandingAmount())
            ->setOutstandingAmountCreated($container->getTotalCreatedOrdersAmount())
            ->setOutstandingAmountLate($container->getTotalLateOrdersAmount())
            ->setBankAccountIban($container->getPaymentDetails()->getBankAccountIban())
            ->setBankAccountBic($container->getPaymentDetails()->getBankAccountBic())
            ->setCreatedAt(new \DateTime())
        ;
    }

    public function createExtendedFromContainer(MerchantDebtorContainer $container): MerchantDebtorExtended
    {
        return (new MerchantDebtorExtended())
            ->setMerchantDebtorId($container->getMerchantDebtor()->getId())
            ->setCompanyId($container->getDebtorCompany()->getId())
            ->setCompanyUuid($container->getDebtorCompany()->getUuid())
            ->setPaymentId($container->getMerchantDebtor()->getPaymentDebtorId())
            ->setIsWhitelisted($container->getMerchantDebtor()->isWhitelisted())
            ->setIsBlacklisted($container->getDebtorCompany()->isBlacklisted())
            ->setIsTrustedSource($container->getDebtorCompany()->isTrustedSource())
            ->setCrefoId($container->getDebtorCompany()->getCrefoId())
            ->setSchufaId($container->getDebtorCompany()->getSchufaId())
            ->setUuid($container->getMerchantDebtor()->getUuid())
            ->setExternalCode($container->getExternalId())
            ->setName($container->getDebtorCompany()->getName())
            ->setAddressStreet($container->getDebtorCompany()->getAddressStreet())
            ->setAddressHouse($container->getDebtorCompany()->getAddressHouse())
            ->setAddressPostalCode($container->getDebtorCompany()->getAddressPostalCode())
            ->setAddressCity($container->getDebtorCompany()->getAddressCity())
            ->setAddressCountry($container->getDebtorCompany()->getAddressCountry())
            ->setFinancingLimit($container->getFinancialDetails()->getFinancingLimit())
            ->setFinancingPower($this->calculateFinancingPower($container->getDebtorCompany(), $container->getFinancialDetails()))
            ->setOutstandingAmount($container->getPaymentDetails()->getOutstandingAmount())
            ->setOutstandingAmountCreated($container->getTotalCreatedOrdersAmount())
            ->setOutstandingAmountLate($container->getTotalLateOrdersAmount())
            ->setBankAccountIban($container->getPaymentDetails()->getBankAccountIban())
            ->setBankAccountBic($container->getPaymentDetails()->getBankAccountBic())
            ->setCreatedAt($container->getMerchantDebtor()->getCreatedAt())
        ;
    }

    public function createListItemFromContainer(MerchantDebtorContainer $container): MerchantDebtorListItem
    {
        return (new MerchantDebtorListItem())
            ->setUuid($container->getMerchantDebtor()->getUuid())
            ->setExternalCode($container->getExternalId())
            ->setName($container->getDebtorCompany()->getName())
            ->setFinancingLimit($container->getFinancialDetails()->getFinancingLimit())
            ->setFinancingPower($this->calculateFinancingPower($container->getDebtorCompany(), $container->getFinancialDetails()))
            ->setBankAccountIban($container->getPaymentDetails()->getBankAccountIban())
            ->setBankAccountBic($container->getPaymentDetails()->getBankAccountBic())
            ->setCreatedAt($container->getMerchantDebtor()->getCreatedAt())
        ;
    }

    /**
     * @param  int                      $total
     * @param  MerchantDebtorListItem[] $items
     * @return MerchantDebtorList
     */
    public function createList(int $total, array $items): MerchantDebtorList
    {
        return (new MerchantDebtorList())
            ->setTotal($total)
            ->setItems(...$items);
    }

    private function calculateFinancingPower(
        DebtorCompany $debtorCompany,
        MerchantDebtorFinancialDetailsEntity $merchantDebtorFinancialDetails
    ): float {
        return min($merchantDebtorFinancialDetails->getFinancingPower(), $debtorCompany->getFinancingPower());
    }

    public function createFromDebtorCompany(DebtorCompany $debtorCompany): MerchantDebtorSynchronizationResponse
    {
        return (new MerchantDebtorSynchronizationResponse())
            ->setDebtorCompany($debtorCompany)
            ->setIsUpdated($debtorCompany->isSynchronized() ?? false);
    }
}
