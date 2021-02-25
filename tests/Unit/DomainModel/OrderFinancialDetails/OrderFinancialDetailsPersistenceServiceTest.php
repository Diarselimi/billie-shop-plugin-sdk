<?php

declare(strict_types=1);

namespace App\Tests\Unit\DomainModel\OrderFinancialDetails;

use App\Application\UseCase\LegacyUpdateOrder\LegacyUpdateOrderRequest;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerRelationLoader;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsFactory;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsPersistenceService;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use App\Tests\Unit\UnitTestCase;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;

class OrderFinancialDetailsPersistenceServiceTest extends UnitTestCase
{
    /** @test */
    public function itShouldCalculateUnshippedAmountWhenAmountIsChanged()
    {
        $finantialDetailsFactory = new OrderFinancialDetailsFactory();
        $repository = $this->createMock(OrderFinancialDetailsRepositoryInterface::class);
        $finantialDetailsPersistenceService = new OrderFinancialDetailsPersistenceService(
            $repository,
            $finantialDetailsFactory
        );

        $orderContainer = new OrderContainer(new OrderEntity(), $this->createMock(OrderContainerRelationLoader::class));
        $orderContainer->setOrderFinancialDetails(
            $finantialDetailsFactory->create(
                1,
                TaxedMoneyFactory::create(500, 50, 10),
                20,
                TaxedMoneyFactory::create(500, 50, 10)
            )
        );
        $changeset = new LegacyUpdateOrderRequest('1', 1);
        $changeset->setAmount(TaxedMoneyFactory::create(200, 20, 5));

        $orderFinancialDetailsEntity = $orderContainer->getOrderFinancialDetails();

        $this->assertTrue($changeset->isAmountChanged());
        $this->assertNotNull($changeset->getAmount());

        $this->assertEquals($orderFinancialDetailsEntity->getAmountGross()->getMoneyValue(), 500.00);
        $this->assertEquals($orderFinancialDetailsEntity->getAmountNet()->getMoneyValue(), 50.00);
        $this->assertEquals($orderFinancialDetailsEntity->getUnshippedAmountGross()->getMoneyValue(), 500.00);
        $this->assertEquals($orderFinancialDetailsEntity->getUnshippedAmountNet()->getMoneyValue(), 50.00);

        $finantialDetailsPersistenceService->updateFinancialDetails($orderContainer, $changeset, 30);

        $orderFinancialDetailsEntity = $orderContainer->getOrderFinancialDetails();

        $this->assertEquals($orderFinancialDetailsEntity->getAmountGross()->getMoneyValue(), 200.00);
        $this->assertEquals($orderFinancialDetailsEntity->getAmountNet()->getMoneyValue(), 20.00);

        $this->assertEquals($orderFinancialDetailsEntity->getUnshippedAmountGross()->getMoneyValue(), 200.00);
        $this->assertEquals($orderFinancialDetailsEntity->getUnshippedAmountNet()->getMoneyValue(), 20.00);
    }

    /** @test */
    public function itShouldNotCalculateUnshippedAmountWhenAmountIsNotChanged()
    {
        $finantialDetailsFactory = new OrderFinancialDetailsFactory();
        $repository = $this->createMock(OrderFinancialDetailsRepositoryInterface::class);
        $finantialDetailsPersistenceService = new OrderFinancialDetailsPersistenceService(
            $repository,
            $finantialDetailsFactory
        );

        $orderContainer = new OrderContainer(new OrderEntity(), $this->createMock(OrderContainerRelationLoader::class));
        $orderContainer->setOrderFinancialDetails(
            $finantialDetailsFactory->create(
                1,
                TaxedMoneyFactory::create(500, 50, 10),
                20,
                TaxedMoneyFactory::create(500, 50, 10)
            )
        );
        $changeset = new LegacyUpdateOrderRequest('1', 1);

        $orderFinancialDetailsEntity = $orderContainer->getOrderFinancialDetails();

        $this->assertEquals($orderFinancialDetailsEntity->getAmountGross()->getMoneyValue(), 500.00);
        $this->assertEquals($orderFinancialDetailsEntity->getAmountNet()->getMoneyValue(), 50.00);
        $this->assertEquals($orderFinancialDetailsEntity->getUnshippedAmountGross()->getMoneyValue(), 500.00);
        $this->assertEquals($orderFinancialDetailsEntity->getUnshippedAmountNet()->getMoneyValue(), 50.00);

        $finantialDetailsPersistenceService->updateFinancialDetails($orderContainer, $changeset, 30);

        $orderFinancialDetailsEntity = $orderContainer->getOrderFinancialDetails();

        $this->assertEquals($orderFinancialDetailsEntity->getAmountGross()->getMoneyValue(), 500.00);
        $this->assertEquals($orderFinancialDetailsEntity->getAmountNet()->getMoneyValue(), 50.00);

        $this->assertEquals($orderFinancialDetailsEntity->getUnshippedAmountGross()->getMoneyValue(), 500.00);
        $this->assertEquals($orderFinancialDetailsEntity->getUnshippedAmountNet()->getMoneyValue(), 50.00);
    }
}
