<?php

namespace App\DomainModel\OrderPayment;

use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Borscht\OrderAmountChangeDTO;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\Order\OrderEntity;

class OrderPaymentForgivenessService
{
    private $paymentsService;

    private $merchantSettingsRepository;

    public function __construct(
        BorschtInterface $paymentsService,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository
    ) {
        $this->paymentsService = $paymentsService;
        $this->merchantSettingsRepository = $merchantSettingsRepository;
    }

    public function begForgiveness(OrderEntity $order, OrderAmountChangeDTO $amountChange): bool
    {
        $outstandingAmount = $amountChange->getOutstandingAmount();
        $merchantSettings = $this->merchantSettingsRepository->getOneByMerchantOrFail($order->getMerchantId());
        $debtorForgivenessThreshold = $merchantSettings->getDebtorForgivenessThreshold();

        if (
            ($debtorForgivenessThreshold <= 0) ||
            ($outstandingAmount <= 0) ||
            ($amountChange->getPaidAmount() <= 0) ||
            ($outstandingAmount > $debtorForgivenessThreshold)
        ) {
            return false;
        }

        $this->paymentsService->confirmPayment($order, $outstandingAmount);

        return true;
    }
}
