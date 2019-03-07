<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\Order\OrderContainer;

class AmountCheck implements CheckInterface
{
    public const NAME = 'amount';

    private const MAX_AMOUNT = 50000;

    private $merchantSettingsRepository;

    public function __construct(MerchantSettingsRepositoryInterface $merchantSettingsRepository)
    {
        $this->merchantSettingsRepository = $merchantSettingsRepository;
    }

    public function check(OrderContainer $order): CheckResult
    {
        $amount = $order->getOrder()->getAmountGross();
        $minAmount = $this->merchantSettingsRepository
            ->getOneByMerchantOrFail($order->getMerchant()->getId())
            ->getMinOrderAmount()
        ;

        $result = ($amount >= $minAmount) && ($amount <= self::MAX_AMOUNT);

        return new CheckResult($result, self::NAME);
    }
}
