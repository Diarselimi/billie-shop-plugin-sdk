<?php

namespace App\DomainModel\Fee;

use App\DomainModel\VatRate\VatRateRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Money\Money;
use Ozean12\Money\Percent;

class FeeCalculator implements LoggingInterface
{
    use LoggingTrait;

    private VatRateRepositoryInterface $vatRateRepository;

    public function __construct(VatRateRepositoryInterface $vatRateRepository)
    {
        $this->vatRateRepository = $vatRateRepository;
    }

    /**
     * @param array|Percent[] $feeRates
     */
    public function calculate(Money $amount, int $duration, array $feeRates): Fee
    {
        $feeRate = $this->getFeeRate($duration, $feeRates);
        $netFeeAmount = $amount->percent($feeRate)->round();
        $taxFeeAmount = $netFeeAmount->percent($this->vatRateRepository->getForDateTime(new \DateTime()))->round();
        $grossFeeAmount = $netFeeAmount->add($taxFeeAmount);

        return new Fee($feeRate, $grossFeeAmount, $netFeeAmount, $taxFeeAmount);
    }

    /**
     * @param array|Percent[] $feeRates
     */
    private function getFeeRate(int $duration, array $feeRates): Percent
    {
        $feeRatesSorted = $feeRates;
        ksort($feeRatesSorted);
        foreach ($feeRatesSorted as $maxDuration => $feeRate) {
            if ($maxDuration >= $duration) {
                return $feeRate;
            }
        }

        throw new FeeCalculationException('Fee rate not found');
    }
}
