<?php

declare(strict_types=1);

namespace App\Tests\Domain\Order;

use App\DomainModel\Order\OrderEntity;
use App\Tests\Unit\UnitTestCase;

class OrderExtendExpirationTest extends UnitTestCase
{
    /**
     * @test
     */
    public function throwExceptionIfNewExpirationIsBeforeCurrentOne(): void
    {
        $order = new OrderEntity(new \DateTimeImmutable('2021-04-01 10:10:10'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('New expiration must be after current one');

        $order->extendExpiration(new \DateTimeImmutable('2021-03-01 10:10:10'));
    }

    /**
     * @test
     */
    public function throwExceptionIfOrderDoesNotHaveInitialExpiration(): void
    {
        $order = new OrderEntity();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot add expiration');

        $order->extendExpiration(new \DateTimeImmutable('2021-03-01 10:10:10'));
    }

    /**
     * @test
     */
    public function extendExpirationIfProvidedOneIsAfterCurrentOne(): void
    {
        $order = new OrderEntity(new \DateTimeImmutable('2021-04-01 10:10:10'));

        $order->extendExpiration(new \DateTimeImmutable('2021-05-01 10:10:10'));

        $this->assertEquals(new \DateTimeImmutable('2021-05-01 10:10:10'), $order->expiration());
    }
}
