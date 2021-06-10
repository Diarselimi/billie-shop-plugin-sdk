<?php

namespace App\DomainModel\Invoice;

use App\DomainModel\Fee\FeeCalculationException;
use App\DomainModel\Fee\FeeCalculatorInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\Infrastructure\Volt\VoltServiceException;
use Ozean12\Money\TaxedMoney\TaxedMoney;

class UpdateInvoiceFeeService
{
    private FeeCalculatorInterface $feeCalculator;

    public function __construct(FeeCalculatorInterface $feeCalculator)
    {
        $this->feeCalculator = $feeCalculator;
    }

    public function updateFee(OrderContainer $orderContainer, Invoice $invoice): void
    {
        try {
            $fee = $this->feeCalculator->getCalculateFee(
                $invoice->getPaymentUuid(),
                $invoice->getAmount()->getGross()->subtract($invoice->getCreditNotes()->getGrossSum()),
                $invoice->getBillingDate(),
                $invoice->getDueDate(),
                $orderContainer->getMerchantSettings()->getFeeRates()
            );
        } catch (VoltServiceException $exception) {
            throw new FeeCalculationException('Call to fee calculation service failed', 0, $exception);
        }

        $invoice
            ->setFeeAmount(new TaxedMoney($fee->getGrossFeeAmount(), $fee->getNetFeeAmount(), $fee->getTaxFeeAmount()))
            ->setFeeRate($fee->getFeeRate())
        ;
    }
}
