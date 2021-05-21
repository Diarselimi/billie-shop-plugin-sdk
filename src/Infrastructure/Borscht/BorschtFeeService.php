<?php

namespace App\Infrastructure\Borscht;

use App\DomainModel\Fee\Fee;
use App\DomainModel\Fee\FeeService;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\VatRate\VatRateRepositoryInterface;
use Ozean12\Money\Money;
use Ozean12\Money\Percent;

//@TODO remove this service when the fee endpoint is ready
class BorschtFeeService implements FeeService
{
    private VatRateRepositoryInterface $vatRateRepository;

    public function __construct(
        VatRateRepositoryInterface $vatRateRepository
    ) {
        $this->vatRateRepository = $vatRateRepository;
    }

    public function getFee(Invoice $invoice): Fee
    {
        $newRate = new Percent($invoice->getFeeRate());

        $vatRate = $this->vatRateRepository->getCurrentRate();
        $grossAmount = new Money($invoice->getFeeAmount());
        $netAmount = (new Money($grossAmount))->divide($vatRate->divide(100)->add(1));
        $taxAmount = $netAmount->percent($vatRate);

        $newFee = new Fee($newRate, $grossAmount, $netAmount, $taxAmount);

        return $newFee;
    }
}
