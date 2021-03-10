<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Repository;

use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntity;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckRepositoryInterface;
use App\Tests\Helpers\FakeDataFiller;
use App\Tests\Helpers\RandomDataTrait;
use App\Tests\Integration\DatabaseTestCase;

class OrderRiskCheckRepositoryTest extends DatabaseTestCase
{
    use RandomDataTrait;
    use FakeDataFiller;

    public function testShouldReturnTheCorrectLatestFailedRiskChecks()
    {
        // Prepare some data
        $order = $this->getRandomOrderEntity();
        $failedChecks = $this->generateSomeFailingOrderRiskChecks($order->getId());
        $this->passRiskChecksForOrder($failedChecks);

        $riskCheckToFail = array_shift($failedChecks);

        $this->failRiskChecksForOrder([$riskCheckToFail]);

        //assert that query returns the correct risk check failed
        $this->assertEquals(
            $riskCheckToFail->getRiskCheckDefinition()->getName(),
            $this->getContainer()->get(OrderRiskCheckRepositoryInterface::class)->findLastFailedRiskChecksByOrderId(
                $order->getId()
            )->getFirstHardDeclined()->getName()
        );
    }

    /**
     * @param OrderRiskCheckEntity[] $riskCheckEntities
     */
    private function passRiskChecksForOrder(array $riskCheckEntities)
    {
        foreach ($riskCheckEntities as $riskCheckEntity) {
            $this->insertNewRiskChecks($riskCheckEntity->getOrderId(), true, [$riskCheckEntity]);
        }
    }

    /**
     * @param OrderRiskCheckEntity[] $riskCheckEntities
     */
    private function failRiskChecksForOrder(array $riskCheckEntities)
    {
        foreach ($riskCheckEntities as $riskCheckEntity) {
            $this->insertNewRiskChecks($riskCheckEntity->getOrderId(), false, [$riskCheckEntity]);
        }
    }

    private function insertNewRiskChecks(int $orderId, bool $isPassed, array $riskChecks)
    {
        foreach ($riskChecks as $check) {
            $orderRiskCheck = new OrderRiskCheckEntity();
            $orderRiskCheck->setOrderId($orderId);
            $orderRiskCheck->setIsPassed($isPassed);
            $orderRiskCheck->setRiskCheckDefinition($check->getRiskCheckDefinition());

            $this->createOrderRiskCheck($orderRiskCheck);
        }
    }
}
