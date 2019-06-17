<?php

namespace spec\App\Application\UseCase\CheckoutSessionConfirmOrder;

use App\Application\Exception\CheckoutSessionConfirmException;
use App\Application\UseCase\CheckoutSessionConfirmOrder\CheckoutSessionConfirmOrderRequest;
use App\Application\UseCase\CheckoutSessionConfirmOrder\CheckoutSessionConfirmUseCase;
use App\Application\UseCase\CreateOrder\Request\CreateOrderAmountRequest;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CheckoutSessionConfirmUseCaseSpec extends ObjectBehavior
{
    public function let(
        OrderResponseFactory $orderResponseFactory,
        OrderContainerFactory $orderContainerFactory,
        OrderStateManager $orderStateManager,
        ValidatorInterface $validator,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $this->beConstructedWith(...func_get_args());

        $this->setValidator($validator);
        $validator->validate(Argument::any(), Argument::any(), Argument::any())->willReturn(new ConstraintViolationList());

        $orderContainerFactory->loadByCheckoutSessionUuid('test123')->willReturn($orderContainer);

        $order->getState()->willReturn(OrderStateManager::STATE_AUTHORIZED);
        $orderContainer->getOrder()->willReturn($order);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CheckoutSessionConfirmUseCase::class);
    }

    public function it_should_throw_an_exception_if_duration_does_not_match(
        OrderContainer $orderContainer,
        OrderEntity $order,
        OrderFinancialDetailsEntity $orderFinancialDetails
    ) {
        $checkoutSessionUuid = 'test123';
        $duration = 30;
        $amountNet = 100.0;
        $amountTax = 10.0;
        $amountGross = 110.0;

        $request = $this->createRequest($checkoutSessionUuid, $duration, $amountGross, $amountNet, $amountTax);

        $this->mockOrderFinancialDetails($orderFinancialDetails, $duration + 1000);
        $orderContainer->getOrderFinancialDetails()->willReturn($orderFinancialDetails);

        $this->shouldThrow(CheckoutSessionConfirmException::class)->during('execute', [$request, $checkoutSessionUuid]);
    }

    public function it_should_throw_an_exception_if_gross_does_not_match(
        OrderContainer $orderContainer,
        OrderEntity $order,
        OrderFinancialDetailsEntity $orderFinancialDetails
    ) {
        $checkoutSessionUuid = 'test123';
        $duration = 30;
        $amountNet = 100.0;
        $amountTax = 10.0;
        $amountGross = 110.0;

        $request = $this->createRequest($checkoutSessionUuid, $duration, $amountGross, $amountNet, $amountTax);

        $this->mockOrderFinancialDetails($orderFinancialDetails, $duration, $amountGross + 1, $amountNet, $amountTax);
        $orderContainer->getOrderFinancialDetails()->willReturn($orderFinancialDetails);

        $this->shouldThrow(CheckoutSessionConfirmException::class)->during('execute', [$request]);
    }

    public function it_should_throw_an_exception_if_tax_does_not_match(
        OrderContainer $orderContainer,
        OrderEntity $order,
        OrderFinancialDetailsEntity $orderFinancialDetails
    ) {
        $checkoutSessionUuid = 'test123';
        $duration = 30;
        $amountNet = 100.0;
        $amountTax = 10.0;
        $amountGross = 110.0;

        $request = $this->createRequest($checkoutSessionUuid, $duration, $amountGross, $amountNet, $amountTax);

        $this->mockOrderFinancialDetails($orderFinancialDetails, $duration, $amountGross, $amountNet, $amountTax + 1);
        $orderContainer->getOrderFinancialDetails()->willReturn($orderFinancialDetails);

        $this->shouldThrow(CheckoutSessionConfirmException::class)->during('execute', [$request]);
    }

    public function it_should_throw_an_exception_if_net_does_not_match(
        OrderContainer $orderContainer,
        OrderEntity $order,
        OrderFinancialDetailsEntity $orderFinancialDetails
    ) {
        $checkoutSessionUuid = 'test123';
        $duration = 30;
        $amountNet = 100.0;
        $amountTax = 10.0;
        $amountGross = 110.0;

        $request = $this->createRequest($checkoutSessionUuid, $duration, $amountGross, $amountNet, $amountTax);

        $this->mockOrderFinancialDetails($orderFinancialDetails, $duration, $amountGross, $amountNet + 1, $amountTax);
        $orderContainer->getOrderFinancialDetails()->willReturn($orderFinancialDetails);

        $this->shouldThrow(CheckoutSessionConfirmException::class)->during('execute', [$request, $checkoutSessionUuid]);
    }

    public function it_should_create_order_if_everything_matches(
        OrderContainer $orderContainer,
        OrderResponseFactory $orderResponseFactory,
        OrderEntity $order,
        OrderFinancialDetailsEntity $orderFinancialDetails
    ) {
        $checkoutSessionUuid = 'test123';
        $duration = 30;
        $amountNet = 100.0;
        $amountTax = 10.0;
        $amountGross = 110.0;

        $request = $this->createRequest($checkoutSessionUuid, $duration, $amountGross, $amountNet, $amountTax);

        $this->mockOrderFinancialDetails($orderFinancialDetails, $duration, $amountGross, $amountNet, $amountTax);
        $orderContainer->getOrderFinancialDetails()->willReturn($orderFinancialDetails);

        $orderResponseFactory->create($orderContainer)->shouldBeCalled()->willReturn(new OrderResponse());
        $this->shouldNotThrow(\Exception::class)->during('execute', [$request, $checkoutSessionUuid]);
    }

    private function mockOrderFinancialDetails(
        OrderFinancialDetailsEntity $orderFinancialDetails,
        int $duration,
        float $amountGross = 0.0,
        float $amountNet = 0.0,
        float $amountTax = 0.0
    ) {
        $orderFinancialDetails->getDuration()->willReturn($duration);
        $orderFinancialDetails->getAmountNet()->willReturn($amountNet);
        $orderFinancialDetails->getAmountTax()->willReturn($amountTax);
        $orderFinancialDetails->getAmountGross()->willReturn($amountGross);
    }

    private function createRequest(
        string $sessionId,
        int $duration,
        float $amountGross = 0.0,
        float $amountNet = 0.0,
        float $amountTax = 0.0
    ): CheckoutSessionConfirmOrderRequest {
        return (new CheckoutSessionConfirmOrderRequest())
            ->setDuration($duration)
            ->setAmount((new CreateOrderAmountRequest())->setGross($amountGross)->setNet($amountNet)->setTax($amountTax))
            ->setSessionUuid($sessionId)
        ;
    }
}
