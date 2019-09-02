<?php

namespace App\DomainModel\MerchantDebtor\Limits;

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

    private $debtorLimitManager;

    public function __construct(
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository,
        OrderRepositoryInterface $orderRepository,
        DebtorLimitManagerInterface $debtorLimitManager
    ) {
        $this->merchantDebtorFinancialDetailsRepository = $merchantDebtorFinancialDetailsRepository;
        $this->orderRepository = $orderRepository;
        $this->debtorLimitManager = $debtorLimitManager;
    }

    public function isEnough(OrderContainer $container): bool
    {
        $amount = $container->getOrderFinancialDetails()->getAmountGross();

        return $container->getMerchantDebtorFinancialDetails()->getFinancingPower() >= $amount
            && $container->getDebtorCompany()->getFinancingPower() >= $amount
        ;
    }

    public function lock(OrderContainer $container): void
    {
        $debtorUuid = $container->getDebtorCompany()->getUuid();
        $financingDetails = $container->getMerchantDebtorFinancialDetails();
        $amount = $container->getOrderFinancialDetails()->getAmountGross();

        $this->debtorLimitManager->lockDebtorLimit($debtorUuid, $amount);

        $financingDetails->reduceFinancingPower($amount);
        $this->merchantDebtorFinancialDetailsRepository->insert($financingDetails);
    }

    public function unlock(OrderContainer $container, float $amount = null): void
    {
        $financingDetails = $container->getMerchantDebtorFinancialDetails();
        $amount = $amount === null ? $container->getOrderFinancialDetails()->getAmountGross() : $amount;
        $debtorUuid = $container->getDebtorCompany()->getUuid();

        $this->debtorLimitManager->unlockDebtorLimit($debtorUuid, $amount);

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

    public function setDebtorLimitManager(DebtorLimitManagerInterface $debtorLimitManager): MerchantDebtorLimitsService
    {
        $this->debtorLimitManager = $debtorLimitManager;

        return $this;
    }
}
