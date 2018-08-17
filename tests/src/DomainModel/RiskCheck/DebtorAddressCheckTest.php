<?php

namespace App\Tests\src\DomainModel\RiskCheck;

use App\DomainModel\RiskCheck\Checker\DebtorAddressCheck;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class DebtorAddressCheckTest extends TestCase
{
    /**
     * @var DebtorAddressCheck
     */
    private $check;

    public function setUp()
    {
        $this->check = new DebtorAddressCheck();
        $this->check->setLogger(new NullLogger());
    }

    /**
     * Test is house match
     * When we do the address check
     * We have to check that house is match
     *
     * @dataProvider houseMatchProvider
     */
    public function testIsHouseMatch(string $houseFromRegistry, string $houseFromOrder, bool $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->check->isHouseMatch($houseFromRegistry, $houseFromOrder));
    }

    public function houseMatchProvider(): array
    {
        return [
            ['1', '1', true], // simple house numbers
            ['1', ' 1 ', true],
            [' 1 ', '1', true],
            ['1', '1a', true],
            ['1a', '1', true],
            ['1foo', '1bar', true],
            ['1 foo', '1 bar', true],
            ['1 5', '1 2', true],

            ['1', '1-2', true], // ranges
            ['1', '1 - 2', true],
            [' 1 ', ' 1 - 2 ', true],
            ['1a', '1-2', true],
            ['1-1', '1', true],

            ['2-6', '4', true],
            ['2 - 6', ' 4 ', true],
            ['2-   6', '4a', true],
            ['2    - 6', '4 56', true],

            ['2-6', '3-5', true],
            ['3-5', '2-6', true],
            [' 2 - 6 ', '6 - 7', true],
            ['1-   5', '4   - 10 ', true],

            ['1', '', false], // negative cases
            ['', '11', false],
            ['', '', false],
            ['1', '11', false],
            ['12', '11', false],
            ['a5', 'a5', false],
            ['1-10', '11-20', false],
            ['1-1', '11', false],
            ['1-5a', '5', false],
            ['1-5a', '5a', false],
        ];
    }
}
