<?php

namespace App\DomainModel\MerchantDebtor\Limits;

use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Order\OrderContainer;
use App\Infrastructure\Alfred\AlfredRequestException;

class MerchantDebtorLimitsService
{
    private $companyService;

    private $merchantDebtorFinancialDetailsRepository;

    public function __construct(
        CompaniesServiceInterface $companyService,
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository
    ) {
        $this->companyService = $companyService;
        $this->merchantDebtorFinancialDetailsRepository = $merchantDebtorFinancialDetailsRepository;
    }

    public function isEnough(OrderContainer $container): bool
    {
        $amount = $container->getOrder()->getAmountGross();

        return $container->getMerchantDebtorFinancialDetails()->getFinancingPower() >= $amount
            && $container->getMerchantDebtor()->getDebtorCompany()->getFinancingPower() >= $amount
        ;
    }

    public function lock(OrderContainer $container): void
    {
        $debtorId = $container->getMerchantDebtor()->getDebtorId();
        $financingDetails = $container->getMerchantDebtorFinancialDetails();
        $amount = $container->getOrder()->getAmountGross();

        try {
            $this->companyService->lockDebtorLimit($debtorId, $amount);

            $financingDetails->reduceFinancingPower($amount);
            $this->merchantDebtorFinancialDetailsRepository->insert($financingDetails);
        } catch (AlfredRequestException | \LogicException $exception) {
            throw new MerchantDebtorLimitsException();
        }
    }

    public function unlock(OrderContainer $container, float $amount = null): void
    {
        $debtorId = $container->getMerchantDebtor()->getDebtorId();
        $financingDetails = $container->getMerchantDebtorFinancialDetails();
        $amount = $amount === null ? $container->getOrder()->getAmountGross() : $amount;

        try {
            $this->companyService->unlockDebtorLimit($debtorId, $amount);

            $financingDetails->increaseFinancingPower($amount);
            $this->merchantDebtorFinancialDetailsRepository->insert($financingDetails);
        } catch (AlfredRequestException | \LogicException $exception) {
            throw new MerchantDebtorLimitsException();
        }
    }
}
