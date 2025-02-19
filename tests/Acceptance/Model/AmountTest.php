<?php

namespace Billie\Sdk\Tests\Acceptance\Model;

use Billie\Sdk\Exception\Validation\InvalidFieldValueException;
use Billie\Sdk\Model\Amount;
use RuntimeException;

class AmountTest extends AbstractModelTestCase
{
    public function testToArray()
    {
        $data = (new Amount())
            ->setGross(100.00)
            ->setNet(50.50)
            ->toArray();

        static::assertEquals(100, $data['gross']);
        static::assertEquals(50.50, $data['net']);
        static::assertEquals(49.50, $data['tax']);
    }

    public function testTaxCalculation()
    {
        $model = (new Amount())
            ->setGross(119.00)
            ->setTaxRate(19);
        static::assertEquals(100, $model->getNet());

        $model = (new Amount())
            ->setNet(100.00)
            ->setTaxRate(19);
        static::assertEquals(119, $model->getGross());

        // an error should occur cause the values does not match together
        $this->expectException(InvalidFieldValueException::class);
        (new Amount())
            ->setNet(100.00)
            ->setGross(100)
            ->setTaxRate(19);
        // test if execution has been thrown.
        static::assertNull($this->getExpectedException());

        // an error should occur cause the gross-amount is set after the tax-rate
        $this->expectException(RuntimeException::class);
        (new Amount())
            ->setTaxRate(19)
            ->setGross(100);
        // test if execution has been thrown.
        static::assertNull($this->getExpectedException());

        // an error should occur cause the net-amount is set after the tax-rate
        $this->expectException(RuntimeException::class);
        (new Amount())
            ->setTaxRate(19)
            ->setNet(100);
        // test if execution has been thrown.
        static::assertNull($this->getExpectedException());
    }
}
