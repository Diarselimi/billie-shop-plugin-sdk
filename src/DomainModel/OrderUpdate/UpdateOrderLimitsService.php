<?php

namespace App\DomainModel\OrderUpdate;

use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use Ozean12\Money\Money;

class UpdateOrderLimitsService
{
    private MerchantRepository $merchantRepository;

    private MerchantDebtorLimitsService $merchantDebtorLimitsService;

    public function __construct(
        MerchantRepository $merchantRepository,
        MerchantDebtorLimitsService $merchantDebtorLimitsService
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->merchantDebtorLimitsService = $merchantDebtorLimitsService;
    }

    public function updateLimitAmounts(OrderContainer $orderContainer, Money $amount): void
    {
        $amountGrossDiff = $orderContainer
            ->getOrderFinancialDetails()
            ->getAmountGross()
            ->subtract($amount);

        // unlock merchant-debtor limit
        $this->merchantDebtorLimitsService->unlock($orderContainer, $amountGrossDiff);

        // unlock merchant limit
        $merchant = $orderContainer->getMerchant();
        $merchant->increaseFinancingLimit($amountGrossDiff);
        $this->merchantRepository->update($merchant);
    }
}
