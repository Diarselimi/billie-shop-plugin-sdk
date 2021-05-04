<?php

namespace App\DomainModel\OrderUpdate;

use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use Ozean12\Money\TaxedMoney\TaxedMoney;

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

    public function updateLimitAmounts(OrderContainer $orderContainer, TaxedMoney $amount): void
    {
        $amountGrossDiff = $orderContainer
            ->getOrderFinancialDetails()
            ->getAmountGross()
            ->subtract($amount->getGross());

        // unlock merchant-debtor limit
        $this->merchantDebtorLimitsService->unlock($orderContainer, $amountGrossDiff);

        // unlock merchant limit
        $merchant = $orderContainer->getMerchant();
        $merchant->increaseFinancingLimit($amountGrossDiff);
        $this->merchantRepository->update($merchant);
    }
}
