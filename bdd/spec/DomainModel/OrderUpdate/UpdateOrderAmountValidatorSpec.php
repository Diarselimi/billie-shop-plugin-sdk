<?php

namespace spec\App\DomainModel\OrderUpdate;

use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderUpdate\UpdateOrderException;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;
use PhpSpec\ObjectBehavior;

class UpdateOrderAmountValidatorSpec extends ObjectBehavior
{
    private const STATE = 'DUMMY_STATE';

    public function it_should_decrease_the_amount(
        OrderContainer $orderContainer
    ) {
        $invoiceCollection = new InvoiceCollection([]);
        $orderContainer->getInvoices()->willReturn($invoiceCollection);
        $newAmount = TaxedMoneyFactory::create(100.0, 81.0, 19.0);

        $financialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(200.0))
            ->setAmountNet(new Money(162.0))
            ->setAmountTax(new Money(38.0));
        $orderContainer->getOrderFinancialDetails()->willReturn($financialDetails);

        $order = (new OrderEntity())
            ->setState(self::STATE);
        $orderContainer->getOrder()->willReturn($order);

        $newAmount = $this->getValidatedValue($orderContainer, $newAmount, [self::STATE]);
        $newAmount->getGross()->getMoneyValue()->shouldBe(100.0);
        $newAmount->getNet()->getMoneyValue()->shouldBe(81.0);
        $newAmount->getTax()->getMoneyValue()->shouldBe(19.0);
    }

    public function it_should_not_increase_the_amount(
        OrderContainer $orderContainer
    ) {
        $invoiceCollection = new InvoiceCollection([]);
        $newAmount = TaxedMoneyFactory::create(100.0, 81.0, 19.0);

        $financialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(50.0))
            ->setAmountNet(new Money(40.5))
            ->setAmountTax(new Money(9.5))
            ->setUnshippedAmountGross(new Money())
            ->setUnshippedAmountNet(new Money())
            ->setUnshippedAmountTax(new Money());
        $orderContainer->getOrderFinancialDetails()->willReturn($financialDetails);
        $orderContainer->getInvoices()->willReturn($invoiceCollection);

        $order = (new OrderEntity())
            ->setState(self::STATE);
        $orderContainer->getOrder()->willReturn($order);

        $this
            ->shouldThrow(UpdateOrderException::class)
            ->during('getValidatedValue', [$orderContainer, $newAmount, [self::STATE]]);
    }
}
