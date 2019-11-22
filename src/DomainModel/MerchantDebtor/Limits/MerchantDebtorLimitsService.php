<?php

namespace App\DomainModel\MerchantDebtor\Limits;

use App\DomainModel\DebtorLimit\DebtorLimitServiceInterface;
use App\DomainModel\DebtorLimit\DebtorLimitServiceRequestException;
use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class MerchantDebtorLimitsService implements LoggingInterface
{
    use LoggingTrait;

    private $merchantDebtorFinancialDetailsRepository;

    private $orderRepository;

    private $debtorLimitService;

    public function __construct(
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository,
        OrderRepositoryInterface $orderRepository,
        DebtorLimitServiceInterface $debtorLimitService
    ) {
        $this->merchantDebtorFinancialDetailsRepository = $merchantDebtorFinancialDetailsRepository;
        $this->orderRepository = $orderRepository;
        $this->debtorLimitService = $debtorLimitService;
    }

    public function isEnough(OrderContainer $container): bool
    {
        $debtorCompanyUuid = $container->getDebtorCompany()->getUuid();
        $amount = $container->getOrderFinancialDetails()->getAmountGross();

        return $this->debtorLimitService->check($debtorCompanyUuid, $amount)
            && $container->getMerchantDebtorFinancialDetails()->getFinancingPower() >= $amount
        ;
    }

    public function lock(OrderContainer $container): void
    {
        $debtorCompanyUuid = $container->getDebtorCompany()->getUuid();
        $financingDetails = $container->getMerchantDebtorFinancialDetails();
        $amount = $container->getOrderFinancialDetails()->getAmountGross();

        try {
            $this->debtorLimitService->lock($debtorCompanyUuid, $amount);
        } catch (DebtorLimitServiceRequestException $exception) {
            throw new MerchantDebtorLimitsException("Limit service call was unsuccessful", null, $exception);
        }

        $financingDetails->reduceFinancingPower($amount);
        $this->merchantDebtorFinancialDetailsRepository->insert($financingDetails);
    }

    public function unlock(OrderContainer $container, float $amount = null): void
    {
        $financingDetails = $container->getMerchantDebtorFinancialDetails();
        $amount = $amount === null ? $container->getOrderFinancialDetails()->getAmountGross() : $amount;
        $debtorCompanyUuid = $container->getDebtorCompany()->getUuid();

        try {
            $this->debtorLimitService->release($debtorCompanyUuid, $amount);
        } catch (DebtorLimitServiceRequestException $exception) {
            throw new MerchantDebtorLimitsException("Limit service call was unsuccessful", null, $exception);
        }

        $financingDetails->increaseFinancingPower($amount);
        $this->merchantDebtorFinancialDetailsRepository->insert($financingDetails);
    }

    public function recalculate(OrderContainer $orderContainer): void
    {
        $merchantDebtorId = $orderContainer->getMerchantDebtor()->getId();
        $merchantSettingLimit = $orderContainer->getMerchantSettings()->getDebtorFinancingLimit();

        $merchantDebtorFinancialDetails = $orderContainer->getMerchantDebtorFinancialDetails();
        $currentLimit = $merchantDebtorFinancialDetails->getFinancingLimit();
        $currentFinancingPower = $merchantDebtorFinancialDetails->getFinancingPower();

        if ($currentLimit >= $merchantSettingLimit) {
            return;
        }

        $completeOrdersCount = $this->orderRepository->getOrdersCountByMerchantDebtorAndState(
            $merchantDebtorId,
            OrderStateManager::STATE_COMPLETE
        );

        if ($completeOrdersCount !== 1) {
            return;
        }

        $newFinancingPower = $currentFinancingPower + ($merchantSettingLimit - $currentLimit);

        $this->logInfo('Merchant debtor smart limit increased from {old} to {new}', [
            'old' => $currentLimit,
            'new' => $merchantSettingLimit,
        ]);

        $merchantDebtorFinancialDetails
            ->setFinancingLimit($merchantSettingLimit)
            ->setFinancingPower($newFinancingPower)
        ;

        $this->merchantDebtorFinancialDetailsRepository->insert($merchantDebtorFinancialDetails);
    }
}
