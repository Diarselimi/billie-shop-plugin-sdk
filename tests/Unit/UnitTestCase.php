<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use DateTimeInterface;
use Ozean12\Money\Money;
use PHPUnit\Framework\TestCase;

abstract class UnitTestCase extends TestCase
{
    protected static function assertDateEquals(DateTimeInterface $expected, DateTimeInterface $actual): void
    {
        self::assertEquals($expected->format('Y-m-d H:i:s'), $actual->format('Y-m-d H:i:s'));
    }

    protected static function assertMoneyEquals(Money $expected, Money $actual): void
    {
        self::assertEquals($expected->getMoneyValue(), $actual->getMoneyValue());
    }
}
