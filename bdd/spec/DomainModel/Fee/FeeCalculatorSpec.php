<?php

namespace spec\App\DomainModel\Fee;

use App\DomainModel\Fee\FeeCalculationException;
use App\DomainModel\Fee\FeeCalculator;
use App\DomainModel\VatRate\VatRateRepositoryInterface;
use Ozean12\Money\Money;
use Ozean12\Money\Percent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class FeeCalculatorSpec extends ObjectBehavior
{
    public function let(
        VatRateRepositoryInterface $vatRateRepository,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($vatRateRepository);
        $this->setLogger($logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FeeCalculator::class);
    }

    public function it_should_calculate_the_fee(VatRateRepositoryInterface $vatRateRepository)
    {
        $vatRateRepository->getForDateTime(Argument::type(\DateTime::class))->shouldBeCalled()->willReturn(new Percent(10));

        foreach ($this->getTestCases() as $testCase) {
            [$amount, $duration, $feeRates, $expectedFeeRate, $expectedNetFeeAmount, $expectedTaxFeeAmount, $expectedGrossFeeAmount] = $testCase;

            $fee = $this->calculate($amount, $duration, $feeRates);

            Assert::true($fee->getFeeRate()->equals($expectedFeeRate)->getWrappedObject());
            Assert::true($fee->getNetFeeAmount()->equals($expectedNetFeeAmount)->getWrappedObject());
            Assert::true($fee->getTaxFeeAmount()->equals($expectedTaxFeeAmount)->getWrappedObject());
            Assert::true($fee->getGrossFeeAmount()->equals($expectedGrossFeeAmount)->getWrappedObject());
        }
    }

    public function it_should_throw_exception_if_fee_rate_not_found(VatRateRepositoryInterface $vatRateRepository)
    {
        $vatRateRepository->getForDateTime(Argument::type(\DateTime::class))->shouldNotBeCalled();

        $this->shouldThrow(FeeCalculationException::class)->during('calculate', [new Money(1000), 31, [30 => new Percent(1)]]);
    }

    private function getTestCases(): array
    {
        return [
            // amount, duration, fee rates, expected fee rate, expected fee amount net, expected fee amount tax, expected fee amount gross
            [new Money(1000), 10, [30 => new Percent(1)], new Percent(1), new Money(10), new Money(1), new Money(11)],
            [new Money(1000), 40, [60 => new Percent(3), 30 => new Percent(1), 45 => new Percent(2)], new Percent(2), new Money(20), new Money(2), new Money(22)],
            [new Money(567.99), 31, [30 => new Percent(1), 31 => new Percent(1.1)], new Percent(1.1), new Money(6.25), new Money(0.62), new Money(6.87)],
        ];
    }
}
