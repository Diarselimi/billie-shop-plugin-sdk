<?php

namespace App\DomainModel\SynchronizeInvoices;

use App\DomainModel\VatRate\VatRateRepositoryInterface;
use Ozean12\Money\Money;

class NetTaxCalculatorService
{
    private VatRateRepositoryInterface $varRateRepository;

    public function __construct(VatRateRepositoryInterface $varRateRepository)
    {
        $this->varRateRepository = $varRateRepository;
    }

    public function getNet(float $gross, \DateTime $feeDate): Money
    {
        $vatRate = new Money($this->varRateRepository->getForDateTime($feeDate));

        return (new Money($gross))->divide($vatRate->divide(100)->add(1));
    }

    public function getTax(float $gross, \DateTime $feeDate): Money
    {
        $vatRate = $this->varRateRepository->getForDateTime($feeDate);

        return $this->getNet($gross, $feeDate)->percent($vatRate);
    }
}
