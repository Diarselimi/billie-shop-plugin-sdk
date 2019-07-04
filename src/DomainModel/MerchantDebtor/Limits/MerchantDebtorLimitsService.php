<?php

namespace App\DomainModel\MerchantDebtor\Limits;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\Infrastructure\Alfred\AlfredRequestException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class MerchantDebtorLimitsService implements LoggingInterface
{
    use LoggingTrait;

    private $companyService;

    private $merchantDebtorFinancialDetailsRepository;

    private $orderRepository;

    public function __construct(
        CompaniesServiceInterface $companyService,
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->companyService = $companyService;
        $this->merchantDebtorFinancialDetailsRepository = $merchantDebtorFinancialDetailsRepository;
        $this->orderRepository = $orderRepository;
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
        $debtorId = $container->getMerchantDebtor()->getDebtorId();
        $financingDetails = $container->getMerchantDebtorFinancialDetails();
        $amount = $container->getOrderFinancialDetails()->getAmountGross();

        try {
            $this->companyService->lockDebtorLimit($debtorId, $amount);

            $financingDetails->reduceFinancingPower($amount);
            $this->merchantDebtorFinancialDetailsRepository->insert($financingDetails);
        } catch (AlfredRequestException $exception) {
            throw new MerchantDebtorLimitsException('Lock debtor limit failed: ' . $exception->getMessage());
        }
    }

    public function unlock(OrderContainer $container, float $amount = null): void
    {
        $debtorId = $container->getMerchantDebtor()->getDebtorId();
        $financingDetails = $container->getMerchantDebtorFinancialDetails();
        $amount = $amount === null ? $container->getOrderFinancialDetails()->getAmountGross() : $amount;

        try {
            $this->companyService->unlockDebtorLimit($debtorId, $amount);

            $financingDetails->increaseFinancingPower($amount);
            $this->merchantDebtorFinancialDetailsRepository->insert($financingDetails);
        } catch (AlfredRequestException $exception) {
            throw new MerchantDebtorLimitsException('Unlock debtor limit failed: ' . $exception->getMessage());
        }
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
