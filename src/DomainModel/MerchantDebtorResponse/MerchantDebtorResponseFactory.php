<?php

namespace App\DomainModel\MerchantDebtorResponse;

use App\DomainModel\Payment\DebtorPaymentDetailsDTO;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorFinancialDetailsEntity;

class MerchantDebtorResponseFactory
{
    public function create(
        string $merchantExternalId,
        MerchantDebtorEntity $merchantDebtor,
        DebtorCompany $company,
        DebtorPaymentDetailsDTO $paymentDetails,
        MerchantDebtorFinancialDetailsEntity $financialDetails,
        float $totalCreatedOrdersAmount,
        float $totalLateOrdersAmount
    ): MerchantDebtor {
        return (new MerchantDebtor())
            ->setUuid($merchantDebtor->getUuid())
            ->setExternalCode($merchantExternalId)
            ->setName($company->getName())
            ->setAddressStreet($company->getAddressStreet())
            ->setAddressHouse($company->getAddressHouse())
            ->setAddressPostalCode($company->getAddressPostalCode())
            ->setAddressCity($company->getAddressCity())
            ->setAddressCountry($company->getAddressCountry())
            ->setFinancingLimit($financialDetails->getFinancingLimit())
            ->setFinancingPower($this->calculateFinancingPower($company, $financialDetails))
            ->setOutstandingAmount($paymentDetails->getOutstandingAmount())
            ->setOutstandingAmountCreated($totalCreatedOrdersAmount)
            ->setOutstandingAmountLate($totalLateOrdersAmount)
            ->setBankAccountIban($paymentDetails->getBankAccountIban())
            ->setBankAccountBic($paymentDetails->getBankAccountBic())
            ->setCreatedAt(new \DateTime());
    }

    public function createFromContainer(MerchantDebtorContainer $container): MerchantDebtor
    {
        return $this->create(
            $container->getMerchantExternalId(),
            $container->getMerchantDebtor(),
            $container->getCompany(),
            $container->getPaymentDetails(),
            $container->getFinancialDetails(),
            $container->getTotalCreatedOrdersAmount(),
            $container->getTotalLateOrdersAmount()
        );
    }

    public function createExtended(
        string $merchantExternalId,
        MerchantDebtorEntity $merchantDebtor,
        DebtorCompany $company,
        DebtorPaymentDetailsDTO $paymentDetails,
        MerchantDebtorFinancialDetailsEntity $financialDetails,
        float $totalCreatedOrdersAmount,
        float $totalLateOrdersAmount
    ): MerchantDebtorExtended {
        return (new MerchantDebtorExtended())
            ->setMerchantDebtorId($merchantDebtor->getId())
            ->setCompanyId($company->getId())
            ->setCompanyUuid($company->getUuid())
            ->setPaymentId($merchantDebtor->getPaymentDebtorId())
            ->setIsWhitelisted($merchantDebtor->isWhitelisted())
            ->setIsBlacklisted($company->isBlacklisted())
            ->setIsTrustedSource($company->isTrustedSource())
            ->setCrefoId($company->getCrefoId())
            ->setSchufaId($company->getSchufaId())
            ->setUuid($merchantDebtor->getUuid())
            ->setExternalCode($merchantExternalId)
            ->setName($company->getName())
            ->setAddressStreet($company->getAddressStreet())
            ->setAddressHouse($company->getAddressHouse())
            ->setAddressPostalCode($company->getAddressPostalCode())
            ->setAddressCity($company->getAddressCity())
            ->setAddressCountry($company->getAddressCountry())
            ->setFinancingLimit($financialDetails->getFinancingLimit())
            ->setFinancingPower($this->calculateFinancingPower($company, $financialDetails))
            ->setOutstandingAmount($paymentDetails->getOutstandingAmount())
            ->setOutstandingAmountCreated($totalCreatedOrdersAmount)
            ->setOutstandingAmountLate($totalLateOrdersAmount)
            ->setBankAccountIban($paymentDetails->getBankAccountIban())
            ->setBankAccountBic($paymentDetails->getBankAccountBic())
            ->setCreatedAt($merchantDebtor->getCreatedAt());
    }

    public function createExtendedFromContainer(MerchantDebtorContainer $container): MerchantDebtorExtended
    {
        return $this->createExtended(
            $container->getMerchantExternalId(),
            $container->getMerchantDebtor(),
            $container->getCompany(),
            $container->getPaymentDetails(),
            $container->getFinancialDetails(),
            $container->getTotalCreatedOrdersAmount(),
            $container->getTotalLateOrdersAmount()
        );
    }

    public function createListItem(
        string $merchantExternalId,
        MerchantDebtorEntity $merchantDebtor,
        DebtorCompany $company,
        MerchantDebtorFinancialDetailsEntity $financialDetails,
        DebtorPaymentDetailsDTO $paymentDetails
    ): MerchantDebtorListItem {
        return (new MerchantDebtorListItem())
            ->setUuid($merchantDebtor->getUuid())
            ->setExternalCode($merchantExternalId)
            ->setName($company->getName())
            ->setFinancingLimit($financialDetails->getFinancingLimit())
            ->setFinancingPower($this->calculateFinancingPower($company, $financialDetails))
            ->setBankAccountIban($paymentDetails->getBankAccountIban())
            ->setBankAccountBic($paymentDetails->getBankAccountBic())
            ->setCreatedAt($merchantDebtor->getCreatedAt())
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
        MerchantDebtorFinancialDetailsEntity $merchantDbtorfinancialDetails
    ): float {
        return min($merchantDbtorfinancialDetails->getFinancingPower(), $debtorCompany->getFinancingPower());
    }
}
