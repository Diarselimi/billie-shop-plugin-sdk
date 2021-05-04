<?php

namespace spec\App\Application\UseCase\CheckoutConfirmOrder;

use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\CheckoutConfirmOrder\CheckoutConfirmOrderRequest;
use App\Application\UseCase\CheckoutConfirmOrder\CheckoutConfirmOrderUseCase;
use App\DomainModel\CheckoutSession\CheckoutOrderMatcherInterface;
use App\DomainModel\CheckoutSession\CheckoutOrderMatcherViolationList;
use App\DomainModel\CheckoutSession\CheckoutOrderRequestDTO;
use App\DomainModel\DebtorCompany\DebtorCompanyRequest;
use App\DomainModel\Order\Lifecycle\ApproveOrderService;
use App\DomainModel\Order\Lifecycle\WaitingOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderResponse\LegacyOrderResponse;
use App\DomainModel\OrderResponse\LegacyOrderResponseFactory;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CheckoutConfirmOrderUseCaseSpec extends ObjectBehavior
{
    public function let(
        LegacyOrderResponseFactory $orderResponseFactory,
        OrderContainerFactory $orderContainerFactory,
        ApproveOrderService $approveOrderService,
        WaitingOrderService $waitingOrderService,
        CheckoutOrderMatcherInterface $dataMatcher,
        OrderRepositoryInterface $orderRepository,
        ValidatorInterface $validator,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $this->beConstructedWith(...func_get_args());

        $this->setValidator($validator);
        $validator->validate(Argument::any(), Argument::any(), Argument::any())
            ->willReturn(new ConstraintViolationList());

        $orderContainer->getOrder()->willReturn($order);
        $orderContainerFactory->loadNotYetConfirmedByCheckoutSessionUuid('test123')->willReturn($orderContainer);

        $order->getState()->willReturn(OrderEntity::STATE_AUTHORIZED);
        $orderContainer->getOrder()->willReturn($order);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CheckoutConfirmOrderUseCase::class);
    }

    public function it_should_throw_an_exception_if_data_does_not_match(
        OrderContainer $orderContainer,
        CheckoutOrderMatcherInterface $dataMatcher
    ) {
        $checkoutSessionUuid = 'test123';
        $duration = 30;
        $amountNet = 100.0;
        $amountTax = 10.0;
        $amountGross = 110.0;

        $request = $this->createRequest($checkoutSessionUuid, $duration, $amountGross, $amountNet, $amountTax);

        $dataMatcher->matches(Argument::type(CheckoutOrderRequestDTO::class), $orderContainer)
            ->shouldBeCalled()
            ->willReturn(new CheckoutOrderMatcherViolationList(['foo' => 'bar']));

        $this->shouldThrow(RequestValidationException::class)->during('execute', [$request, $checkoutSessionUuid]);
    }

    public function it_should_approve_order_if_data_is_valid_and_not_in_pre_waiting(
        OrderContainer $orderContainer,
        LegacyOrderResponseFactory $orderResponseFactory,
        ApproveOrderService $approveOrderService,
        WaitingOrderService $waitingOrderService,
        OrderEntity $order,
        CheckoutOrderMatcherInterface $dataMatcher
    ) {
        $checkoutSessionUuid = 'test123';
        $duration = 30;
        $amountNet = 100.0;
        $amountTax = 10.0;
        $amountGross = 110.0;

        $order->getExternalCode()->willReturn(null);
        $request = $this->createRequest($checkoutSessionUuid, $duration, $amountGross, $amountNet, $amountTax);

        $dataMatcher->matches(Argument::type(CheckoutOrderRequestDTO::class), $orderContainer)
            ->shouldBeCalled()
            ->willReturn(new CheckoutOrderMatcherViolationList());

        $order->isPreWaiting()->shouldBeCalled()->willReturn(false);
        $waitingOrderService->wait($orderContainer)->shouldNotBeCalled();
        $approveOrderService->approve($orderContainer)->shouldBeCalled();

        $orderResponseFactory->create($orderContainer)->shouldBeCalled()->willReturn(new LegacyOrderResponse());
        $this->execute($request);
    }

    public function it_should_move_order_to_waiting_if_data_is_valid_and_in_pre_waiting(
        OrderContainer $orderContainer,
        LegacyOrderResponseFactory $orderResponseFactory,
        ApproveOrderService $approveOrderService,
        WaitingOrderService $waitingOrderService,
        OrderEntity $order,
        CheckoutOrderMatcherInterface $dataMatcher
    ) {
        $checkoutSessionUuid = 'test123';
        $duration = 30;
        $amountNet = 100.0;
        $amountTax = 10.0;
        $amountGross = 110.0;

        $request = $this->createRequest($checkoutSessionUuid, $duration, $amountGross, $amountNet, $amountTax);
        $order->setExternalCode(Argument::any())->willReturn($order);

        $dataMatcher->matches(Argument::type(CheckoutOrderRequestDTO::class), $orderContainer)
            ->shouldBeCalled()
            ->willReturn(new CheckoutOrderMatcherViolationList());

        $order->isPreWaiting()->shouldBeCalled()->willReturn(true);
        $waitingOrderService->wait($orderContainer)->shouldBeCalled();
        $approveOrderService->approve($orderContainer)->shouldNotBeCalled();

        $orderResponseFactory->create($orderContainer)->shouldBeCalled()->willReturn(new LegacyOrderResponse());
        $this->execute($request);
    }

    private function createRequest(
        string $sessionId,
        int $duration,
        float $amountGross = 0.0,
        float $amountNet = 0.0,
        float $amountTax = 0.0
    ): CheckoutConfirmOrderRequest {
        $amount = TaxedMoneyFactory::create($amountGross, $amountNet, $amountTax);
        $debtorCompany = new DebtorCompanyRequest();

        return (new CheckoutConfirmOrderRequest())
            ->setDuration($duration)
            ->setAmount($amount)
            ->setDebtorCompanyRequest($debtorCompany)
            ->setSessionUuid($sessionId);
    }
}
