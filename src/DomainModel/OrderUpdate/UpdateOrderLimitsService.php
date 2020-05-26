<?php

namespace App\DomainModel\OrderUpdate;

use App\Application\UseCase\UpdateOrder\UpdateOrderAmountInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer\OrderContainer;

class UpdateOrderLimitsService
{
    private $merchantRepository;

    private $merchantDebtorLimitsService;

    public function __construct(
        MerchantRepositoryInterface $merchantRepository,
        MerchantDebtorLimitsService $merchantDebtorLimitsService
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->merchantDebtorLimitsService = $merchantDebtorLimitsService;
    }

    public function unlockLimits(
        OrderContainer $orderContainer,
        UpdateOrderAmountInterface $changeSet
    ) {
        $amountGrossDiff = $orderContainer
            ->getOrderFinancialDetails()
            ->getAmountGross()
            ->subtract($changeSet->getAmount()->getGross());

        // unlock merchant-debtor limit
        $this->merchantDebtorLimitsService->unlock($orderContainer, $amountGrossDiff);

        // unlock merchant limit
        $merchant = $orderContainer->getMerchant();
        $merchant->increaseFinancingLimit($amountGrossDiff);
        $this->merchantRepository->update($merchant);
    }
}
