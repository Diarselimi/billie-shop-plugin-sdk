<?php

namespace spec\App\Application\UseCase\PauseOrderDunning;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\PauseOrderDunning\PauseOrderDunningException;
use App\Application\UseCase\PauseOrderDunning\PauseOrderDunningRequest;
use App\Application\UseCase\PauseOrderDunning\PauseOrderDunningUseCase;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\SalesforceInterface;
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
        OrderRepositoryInterface $orderRepository,
        SalesforceInterface $salesforce,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith($orderRepository, $salesforce);

        $validator->validate(Argument::any(), Argument::any(), Argument::any())->willReturn(new ConstraintViolationList());

        $this->setLogger(new NullLogger())->setValidator($validator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(PauseOrderDunningUseCase::class);
    }

    public function it_throws_exception_if_order_doesnt_exist(OrderRepositoryInterface $orderRepository)
    {
        $request = $this->mockRequest(10);

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_ID, self::MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->shouldThrow(OrderNotFoundException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_order_is_not_in_state_late(
        OrderRepositoryInterface $orderRepository,
        OrderEntity $order
    ) {
        $request = $this->mockRequest(10);

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_ID, self::MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order)
        ;

        $order->isLate()->shouldBeCalled()->willReturn(false);

        $this->shouldThrow(PauseOrderDunningException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_salesforce_call_failed(
        OrderRepositoryInterface $orderRepository,
        SalesforceInterface $salesforce,
        OrderEntity $order
    ) {
        $request = $this->mockRequest(10);

        $order->getUuid()->willReturn(self::ORDER_UUID);

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_ID, self::MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order)
        ;

        $order->isLate()->shouldBeCalled()->willReturn(true);

        $salesforce
            ->pauseOrderDunning(self::ORDER_UUID, 10)
            ->shouldBeCalled()
            ->willThrow(SalesforcePauseDunningException::class)
        ;

        $this->shouldThrow(PauseOrderDunningException::class)->during('execute', [$request]);
    }

    public function it_successfully_calls_salesforce_to_pause_order_dunning(
        OrderRepositoryInterface $orderRepository,
        SalesforceInterface $salesforce,
        OrderEntity $order
    ) {
        $request = $this->mockRequest(10);

        $order->getUuid()->willReturn(self::ORDER_UUID);

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_ID, self::MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order)
        ;

        $order->isLate()->shouldBeCalled()->willReturn(true);
        $salesforce->pauseOrderDunning(self::ORDER_UUID, 10)->shouldBeCalled();

        $this->execute($request);
    }

    private function mockRequest(int $numOfDays): PauseOrderDunningRequest
    {
        return new PauseOrderDunningRequest(self::ORDER_ID, self::MERCHANT_ID, $numOfDays);
    }
}
