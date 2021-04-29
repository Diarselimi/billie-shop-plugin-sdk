<?php

namespace App\Tests\Unit\DomainModel\Invoice;

use App\DomainModel\Invoice\Duration;
use App\DomainModel\Invoice\InvalidDurationException;
use App\Tests\Unit\UnitTestCase;
use DateTime;

class DurationTest extends UnitTestCase
{
    public function testItCanBeInstantiatedWithAValidDuration()
    {
        $duration = new Duration(66);
        $this->assertInstanceOf(Duration::class, $duration);
    }

    public function testItCannotBeInstantiatedWithNegativeDuration()
    {
        $this->expectException(InvalidDurationException::class);
        new Duration(-1);
    }

    public function testItCannotBeInstantiatedWithInvalidDuration()
    {
        $this->expectException(InvalidDurationException::class);
        new Duration(140);
    }

    public function testItCanBeAddedToDate()
    {
        $january1st = new DateTime('2021-01-01');
        $oneWeekDuration = new Duration(7);

        $january8th = $oneWeekDuration->addToDate($january1st);
        $diff = $january8th->diff($january1st)->days;

        $this->assertEquals(7, $diff);
    }
}
