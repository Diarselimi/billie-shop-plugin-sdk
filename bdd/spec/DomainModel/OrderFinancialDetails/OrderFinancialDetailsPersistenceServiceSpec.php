<?php

namespace spec\App\DomainModel\OrderFinancialDetails;

use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsFactory;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;
use PhpSpec\ObjectBehavior;

class OrderFinancialDetailsPersistenceServiceSpec extends ObjectBehavior
{
    public function let(
        OrderFinancialDetailsRepositoryInterface $repository,
        OrderFinancialDetailsFactory $factory
    ) {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_updates_financial_details(
        OrderFinancialDetailsRepositoryInterface $repository,
        OrderFinancialDetailsFactory $factory,
        OrderContainer $orderContainer
    ) {
        // Arrange
        $newFinancialDetails = new OrderFinancialDetailsEntity();
        $changeSet = new UpdateOrderRequest('', 1);
        $orderId = 1;
        $duration = 100;
        $factory->create(
            $orderId,
            TaxedMoneyFactory::create(100.0, 81.0, 19.0),
            $duration
        )->willReturn($newFinancialDetails);
        $orderContainer->getOrderFinancialDetails()->willReturn(
            (new OrderFinancialDetailsEntity())
                ->setAmountGross(new Money(100.0))
                ->setAmountNet(new Money(81.0))
                ->setAmountTax(new Money(19.0))
                ->setOrderId($orderId)
        );

        // Assert
        $repository->insert($newFinancialDetails)->shouldBeCalledOnce();
        $orderContainer->setOrderFinancialDetails($newFinancialDetails)->shouldBeCalledOnce();

        // Act
        $this->updateFinancialDetails($orderContainer, $changeSet, $duration);
    }
}
