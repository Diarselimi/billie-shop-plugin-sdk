<?php

namespace spec\App\Application\UseCase\PauseOrderDunning;

use App\Application\UseCase\PauseOrderDunning\PauseOrderDunningException;
use App\Application\UseCase\PauseOrderDunning\PauseOrderDunningRequest;
use App\Application\UseCase\PauseOrderDunning\PauseOrderDunningUseCase;
use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Salesforce\SalesforceInterface;
use App\Infrastructure\Salesforce\Exception\SalesforcePauseDunningException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\NullLogger;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PauseOrderDunningUseCaseSpec extends ObjectBehavior
{
    private const MERCHANT_ID = 1;

    private const ORDER_ID = 'orderId';

    private const ORDER_UUID = 'test-uuid';

    public function let(
        SalesforceInterface $salesforce,
        OrderContainerFactory $orderContainerFactory,
        OrderContainer $orderContainer,
        InvoiceCollection $invoiceCollection,
        OrderEntity $order,
        ValidatorInterface $validator
    ) {
        $orderContainer->getInvoices()->willReturn($invoiceCollection);
        $orderContainer->getOrder()->willReturn($order);
        $invoiceCollection->isEmpty()->willReturn(true);
        $orderContainerFactory->loadByMerchantIdAndExternalIdOrUuid(Argument::cetera())->willReturn($orderContainer);

        $this->beConstructedWith($salesforce, $orderContainerFactory);

        $validator->validate(
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->willReturn(new ConstraintViolationList());

        $this->setLogger(new NullLogger())->setValidator($validator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(PauseOrderDunningUseCase::class);
    }

    public function it_throws_exception_if_order_is_not_in_state_late(
        OrderContainer $orderContainer,
        OrderEntity $order
    ) {
        $request = $this->mockRequest(10);

        $order->isLate()->shouldBeCalled()->willReturn(false);
        $orderContainer->getOrder()->willReturn($order);

        $this->shouldThrow(PauseOrderDunningException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_salesforce_call_failed(
        OrderContainer $orderContainer,
        SalesforceInterface $salesforce,
        OrderEntity $order
    ) {
        $request = $this->mockRequest(10);

        $order->getUuid()->willReturn(self::ORDER_UUID);
        $order->isLate()->shouldBeCalled()->willReturn(true);

        $orderContainer->getOrder()->willReturn($order);

        $salesforce
            ->pauseDunning(Argument::any())
            ->shouldBeCalled()
            ->willThrow(SalesforcePauseDunningException::class);

        $this->shouldThrow(PauseOrderDunningException::class)->during('execute', [$request]);
    }

    public function it_successfully_calls_salesforce_to_pause_order_dunning(
        OrderContainer $orderContainer,
        SalesforceInterface $salesforce,
        OrderEntity $order
    ) {
        $request = $this->mockRequest(10);

        $order->getUuid()->willReturn(self::ORDER_UUID);

        $order->isLate()->shouldBeCalled()->willReturn(true);
        $orderContainer->getOrder()->willReturn($order);

        $salesforce->pauseDunning(Argument::any())->shouldBeCalled();

        $this->execute($request);
    }

    private function mockRequest(int $numOfDays): PauseOrderDunningRequest
    {
        return new PauseOrderDunningRequest(self::ORDER_ID, self::MERCHANT_ID, $numOfDays);
    }
}
