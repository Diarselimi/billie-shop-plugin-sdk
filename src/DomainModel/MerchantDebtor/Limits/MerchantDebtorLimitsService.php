<?php

namespace App\DomainModel\MerchantDebtor\Limits;

use App\DomainModel\DebtorLimit\DebtorLimitServiceInterface;
use App\DomainModel\DebtorLimit\DebtorLimitServiceRequestException;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class MerchantDebtorLimitsService implements LoggingInterface
{
    use LoggingTrait;

    private $debtorLimitService;

    public function __construct(DebtorLimitServiceInterface $debtorLimitService)
    {
        $this->debtorLimitService = $debtorLimitService;
    }

    public function isEnough(OrderContainer $container): bool
    {
        $debtorCompanyUuid = $container->getDebtorCompany()->getUuid();
        $customerCompanyUuid = $container->getMerchant()->getCompanyUuid();
        $amount = $container->getOrderFinancialDetails()->getAmountGross();

        return $this->debtorLimitService->check($debtorCompanyUuid, $customerCompanyUuid, $amount);
    }

    public function lock(OrderContainer $container): void
    {
        $debtorCompanyUuid = $container->getDebtorCompany()->getUuid();
        $customerCompanyUuid = $container->getMerchant()->getCompanyUuid();
        $amount = $container->getOrderFinancialDetails()->getAmountGross();

        try {
            $this->debtorLimitService->lock($debtorCompanyUuid, $customerCompanyUuid, $amount);
        } catch (DebtorLimitServiceRequestException $exception) {
            throw new MerchantDebtorLimitsException("Limit service call was unsuccessful", null, $exception);
        }
    }

    public function unlock(OrderContainer $container, float $amount = null): void
    {
        $amount = $amount === null ? $container->getOrderFinancialDetails()->getAmountGross() : $amount;
        $debtorCompanyUuid = $container->getDebtorCompany()->getUuid();
        $customerCompanyUuid = $container->getMerchant()->getCompanyUuid();

        try {
            $this->debtorLimitService->release($debtorCompanyUuid, $customerCompanyUuid, $amount);
        } catch (DebtorLimitServiceRequestException $exception) {
            throw new MerchantDebtorLimitsException("Limit service call was unsuccessful", null, $exception);
        }
    }
}
