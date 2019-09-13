<?php

namespace spec\App\Helper\Math;

use App\Helper\Math\MoneyConverter;
use PhpSpec\ObjectBehavior;

class MoneyConverterSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(MoneyConverter::class);
    }

    public function it_converts_money_to_int()
    {
        $testCases = [
            [1, 100],
            [0.000001, 0],
            [15.67, 1567],
            [90.123456789, 9012],
            [9.9999999, 999],
        ];

        foreach ($testCases as $testCase) {
            $this->toInt($testCase[0])->shouldReturn($testCase[1]);
        }
    }

    public function it_converts_int_to_money()
    {
        $testCases = [
            [1000, 10.],
            [1, 0.01],
            [1567, 15.67],
            [123456789, 1234567.89],
            [999999, 9999.99],
            [1000001, 10000.01],
        ];

        foreach ($testCases as $testCase) {
            $this->toMoney($testCase[0])->shouldReturn($testCase[1]);
        }
    }
}
