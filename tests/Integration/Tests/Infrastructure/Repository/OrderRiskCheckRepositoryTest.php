<?php

declare(strict_types=1);

namespace App\Tests\Integration\Tests\Infrastructure\Repository;

use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntity;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckRepositoryInterface;
use App\Tests\Integration\DatabaseTestCase;
use App\Tests\Integration\Helpers\FakeDataFiller;
use App\Tests\Integration\Helpers\RandomDataTrait;

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

        $this->assertNotEmpty(
            $failedChecks,
            "Unexpected: \$failedChecks is empty.
            risk_check_definitions table is possibly empty too. Was the table truncated after migrations?"
        );

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
