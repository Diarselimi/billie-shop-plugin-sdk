<?php

namespace App\DomainModel\MerchantDebtor\Limits;

use App\DomainModel\DebtorLimit\DebtorLimitDTO;
use App\DomainModel\DebtorLimit\DebtorLimitServiceInterface;
use App\DomainModel\DebtorLimit\DebtorLimitServiceRequestException;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Money\Money;

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
        $investorUuid = $container->getMerchant()->getInvestorUuid();
        $amount = $container->getOrderFinancialDetails()->getAmountGross()->toFloat();

        return $this->debtorLimitService->check($debtorCompanyUuid, $customerCompanyUuid, $amount, $investorUuid);
    }

    public function lock(OrderContainer $container): void
    {
        $debtorCompanyUuid = $container->getDebtorCompany()->getUuid();
        $customerCompanyUuid = $container->getMerchant()->getCompanyUuid();
        $investorUuid = $container->getMerchant()->getInvestorUuid();
        $amount = $container->getOrderFinancialDetails()->getAmountGross()->toFloat();

        try {
            $this->debtorLimitService->lock($debtorCompanyUuid, $customerCompanyUuid, $investorUuid, $amount);
        } catch (DebtorLimitServiceRequestException $exception) {
            throw new MerchantDebtorLimitsException("Limit service call was unsuccessful", null, $exception);
        }
    }

    public function unlock(OrderContainer $container, Money $amount = null): void
    {
        $amount = $amount ?? $container->getOrderFinancialDetails()->getAmountGross();
        $debtorCompanyUuid = $container->getDebtorCompany()->getUuid();
        $customerCompanyUuid = $container->getMerchant()->getCompanyUuid();
        $investorUuid = $container->getMerchant()->getInvestorUuid();

        try {
            $this->debtorLimitService->release($debtorCompanyUuid, $customerCompanyUuid, $investorUuid, $amount->toFloat());
        } catch (DebtorLimitServiceRequestException $exception) {
            throw new MerchantDebtorLimitsException("Limit service call was unsuccessful", null, $exception);
        }
    }

    public function retrieve(string $debtorCompanyUuid): ? DebtorLimitDTO
    {
        try {
            return $this->debtorLimitService->retrieve($debtorCompanyUuid);
        } catch (DebtorLimitServiceRequestException $exception) {
            return null;
        }
    }
}
