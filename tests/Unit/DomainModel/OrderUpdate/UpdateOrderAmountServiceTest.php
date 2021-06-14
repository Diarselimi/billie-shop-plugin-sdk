<?php

declare(strict_types=1);

namespace App\Tests\Unit\DomainModel\OrderUpdate;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerRelationLoader;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\SalesforceInterface;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use App\DomainModel\OrderUpdate\UpdateOrderAmountException;
use App\DomainModel\OrderUpdate\UpdateOrderAmountService;
use App\DomainModel\OrderUpdate\UpdateOrderLimitsService;
use App\Tests\Helpers\FakeDataFiller;
use App\Tests\Helpers\RandomDataTrait;
use App\Tests\Unit\UnitTestCase;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;

class UpdateOrderAmountServiceTest extends UnitTestCase
{
    use RandomDataTrait, FakeDataFiller;

    private UpdateOrderAmountService $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $salesforce = $this->prophesize(SalesforceInterface::class);
        $salesforce->getOrderCollectionsStatus()->willReturn(null);

        $fnd = $this->getRandomFinantialDetails(500.00);

        $financialDetailsRepository = $this->createMock(OrderFinancialDetailsRepositoryInterface::class);
        $financialDetailsRepository
            ->method('getLatestByOrderUuid')
            ->willReturn($fnd);
        $updateOrderLimitsService = $this->createMock(UpdateOrderLimitsService::class);

        $this->useCase = new UpdateOrderAmountService(
            $salesforce->reveal(),
            $financialDetailsRepository,
            $updateOrderLimitsService
        );
    }

    /**
     * @test
     * @dataProvider amountDataProviderGoodCases
     */
    public function shouldCalculateCorrectAmounts(array $newTaxedMoney, array $expectedTaxedMoney): void
    {
        $order = new OrderEntity();
        $fnd = new OrderFinancialDetailsEntity();
        $this->fillObject($order);
        $this->fillObject($fnd);
        $fnd->setUnshippedAmount(TaxedMoneyFactory::create(300, 295, 5))
            ->setAmountGross(new Money(500))
            ->setAmountNet(new Money(490))
            ->setAmountTax(new Money(10));

        $relationLoader = $this->createMock(OrderContainerRelationLoader::class);
        $orderContainer = new OrderContainer($order, $relationLoader);
        $orderContainer->setOrderFinancialDetails($fnd);

        $this->useCase->update(
            $orderContainer,
            TaxedMoneyFactory::create($newTaxedMoney[0], $newTaxedMoney[1], $newTaxedMoney[2])
        );

        self::assertEquals($orderContainer->getOrderFinancialDetails()->getAmountGross()->toFloat(), $newTaxedMoney[0]);
        self::assertEquals($orderContainer->getOrderFinancialDetails()->getAmountNet()->toFloat(), $newTaxedMoney[1]);
        self::assertEquals($orderContainer->getOrderFinancialDetails()->getAmountTax()->toFloat(), $newTaxedMoney[2]);

        self::assertEquals($orderContainer->getOrderFinancialDetails()->getUnshippedAmountGross()->toFloat(), $expectedTaxedMoney[0]);
        self::assertEquals($orderContainer->getOrderFinancialDetails()->getUnshippedAmountNet()->toFloat(), $expectedTaxedMoney[1]);
        self::assertEquals($orderContainer->getOrderFinancialDetails()->getUnshippedAmountTax()->toFloat(), $expectedTaxedMoney[2]);
    }

    /**
     * @test
     * @dataProvider amountDataProviderBadCases
     */
    public function shouldFailIfAmountsAreNotCorrect(array $newTaxedMoney): void
    {
        $order = new OrderEntity();
        $fnd = new OrderFinancialDetailsEntity();
        $this->fillObject($order);
        $this->fillObject($fnd);
        $fnd->setUnshippedAmount(TaxedMoneyFactory::create(300, 295, 5))
            ->setAmountGross(new Money(500))
            ->setAmountNet(new Money(490))
            ->setAmountTax(new Money(10));

        $relationLoader = $this->createMock(OrderContainerRelationLoader::class);
        $orderContainer = new OrderContainer($order, $relationLoader);
        $orderContainer->setOrderFinancialDetails($fnd);

        $this->expectException(UpdateOrderAmountException::class);
        $this->useCase->update(
            $orderContainer,
            TaxedMoneyFactory::create($newTaxedMoney[0], $newTaxedMoney[1], $newTaxedMoney[2])
        );
    }

    public function amountDataProviderBadCases(): array
    {
        # Initial Unshipped amounts (300, 290, 10)
        # Initial Amounts (500, 490, 10)
        return [
            [[100, 99, 1]], # [gross, net, tax] = new amounts, [gross, net, tax] = expected
            [[0, 0, 0]],
            [[900, 899, 1]],
            [[199, 194, 5]],
        ];
    }

    public function amountDataProviderGoodCases(): array
    {
        # Initial Unshipped amounts (300, 290, 10)
        # Initial Amounts (500, 490, 10)
        return [
            [[200, 195, 5], [0, 0, 0]], # [gross, net, tax] = new amounts, [gross, net, tax] = expected
            [[255, 248, 7], [55, 53, 2]],
            [[300, 292, 8], [100, 97, 3]],
        ];
    }
}
