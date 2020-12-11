<?php

namespace spec\App\Application\UseCase\CreateOrder;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\Application\UseCase\CreateOrder\CreateOrderUseCase;
use App\DomainModel\Order\Lifecycle\ApproveOrderService;
use App\DomainModel\Order\Lifecycle\DeclineOrderService;
use App\DomainModel\Order\Lifecycle\WaitingOrderService;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\Order\IdentifyAndTriggerAsyncIdentification;
use App\DomainModel\Order\NewOrder\OrderCreationDTO;
use App\DomainModel\Order\NewOrder\OrderPersistenceService;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderResponse\OrderResponseV1;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateOrderUseCaseSpec extends ObjectBehavior
{
    public function let(
        OrderPersistenceService $persistNewOrderService,
        OrderContainerFactory $orderContainerFactory,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderRepositoryInterface $orderRepository,
        OrderResponseFactory $orderResponseFactory,
        ApproveOrderService $approveOrderService,
        WaitingOrderService $waitingOrderService,
        DeclineOrderService $declineOrderService,
        IdentifyAndTriggerAsyncIdentification $identifyAndTriggerAsyncIdentification,
        ValidatorInterface $validator,
        OrderEntity $order,
        OrderCreationDTO $newOrderDTO,
        OrderContainer $orderContainer,
        MerchantSettingsEntity $merchantSettings,
        CreateOrderRequest $request
    ) {
        $this->beConstructedWith(...func_get_args());

        $this->setValidator($validator);
        $validator->validate(Argument::any(), Argument::any(), Argument::any())->willReturn(new ConstraintViolationList());

        $persistNewOrderService->persistFromRequest($request)->willReturn($newOrderDTO);
        $orderContainerFactory->createFromNewOrderDTO($newOrderDTO)->willReturn($orderContainer);

        $orderContainer->getMerchantDebtor()->willReturn((new MerchantDebtorEntity())->setId(1));
        $orderContainer->getOrder()->willReturn($order);
        $orderContainer->getMerchantSettings()->willReturn($merchantSettings);
        $order->getCheckoutSessionId()->willReturn(1);
        $order->getId()->willReturn(1);

        $merchantSettings->useExperimentalDebtorIdentification()->willReturn(false);

        $this->mockRequest($request);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CreateOrderUseCase::class);
    }

    public function it_should_be_declined_if_some_pre_identification_check_fail(
        OrderChecksRunnerService $orderChecksRunnerService,
        ApproveOrderService $approveOrderService,
        DeclineOrderService $declineOrderService,
        OrderContainer $orderContainer,
        CreateOrderRequest $request,
        OrderEntity $order,
        OrderResponseFactory $orderResponseFactory
    ) {
        $orderChecksRunnerService->passesPreIdentificationChecks($orderContainer)->shouldBeCalledOnce()->willReturn(false);
        $order->isDeclined()->shouldBeCalledOnce()->willReturn(true);

        $approveOrderService->approve($orderContainer)->shouldNotBeCalled();
        $declineOrderService->decline($orderContainer)->shouldBeCalledOnce();
        $orderResponseFactory->createV1($orderContainer)->shouldBeCalledOnce()->willReturn(new OrderResponseV1());

        $this->execute($request);
    }

    public function it_should_be_declined_if_post_identifications_checks_fail(
        OrderChecksRunnerService $orderChecksRunnerService,
        ApproveOrderService $approveOrderService,
        DeclineOrderService $declineOrderService,
        OrderContainer $orderContainer,
        CreateOrderRequest $request,
        OrderEntity $order,
        OrderResponseFactory $orderResponseFactory,
        IdentifyAndTriggerAsyncIdentification $identifyAndTriggerAsyncIdentification
    ) {
        $merchantDebtor = (new MerchantDebtorEntity())->setId(1);
        $orderContainer->getMerchantDebtor()->willReturn($merchantDebtor);

        $orderChecksRunnerService->passesPreIdentificationChecks($orderContainer)->shouldBeCalledOnce()->willReturn(true);
        $identifyAndTriggerAsyncIdentification->identifyDebtor($orderContainer)->shouldBeCalledOnce()->willReturn(true);
        $orderChecksRunnerService->passesPostIdentificationChecks($orderContainer)->shouldBeCalledOnce()->willReturn(false);

        $order->isDeclined()->shouldBeCalledOnce()->willReturn(true);

        $approveOrderService->approve($orderContainer)->shouldNotBeCalled();
        $declineOrderService->decline($orderContainer)->shouldBeCalledOnce();
        $orderResponseFactory->createV1($orderContainer)->shouldBeCalledOnce()->willReturn(new OrderResponseV1());

        $this->execute($request);
    }

    public function it_should_put_the_order_in_waiting_state_if_has_soft_declinable_checks(
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderContainer $orderContainer,
        CreateOrderRequest $request,
        WaitingOrderService $waitingOrderService,
        OrderEntity $order,
        OrderResponseFactory $orderResponseFactory,
        IdentifyAndTriggerAsyncIdentification $identifyAndTriggerAsyncIdentification
    ) {
        $merchantDebtor = (new MerchantDebtorEntity())->setId(1);
        $orderContainer->getMerchantDebtor()->willReturn($merchantDebtor);

        $orderChecksRunnerService->passesPreIdentificationChecks($orderContainer)->shouldBeCalledOnce()->willReturn(true);
        $identifyAndTriggerAsyncIdentification->identifyDebtor($orderContainer)->shouldBeCalledOnce()->willReturn(true);
        $orderChecksRunnerService->passesPostIdentificationChecks($orderContainer)->shouldBeCalledOnce()->willReturn(true);
        $orderChecksRunnerService->hasFailedSoftDeclinableChecks($orderContainer)->shouldBeCalledOnce()->willReturn(true);

        $order->isDeclined()->shouldBeCalledOnce()->willReturn(false);
        $waitingOrderService->wait($orderContainer)->shouldBeCalledOnce();
        $orderResponseFactory->createV1($orderContainer)->shouldBeCalledOnce()->willReturn(new OrderResponseV1());

        $this->execute($request);
    }

    public function it_should_succeed_if_all_is_fine(
        OrderChecksRunnerService $orderChecksRunnerService,
        ApproveOrderService $approveOrderService,
        DeclineOrderService $declineOrderService,
        OrderContainer $orderContainer,
        CreateOrderRequest $request,
        OrderEntity $order,
        OrderResponseFactory $orderResponseFactory,
        IdentifyAndTriggerAsyncIdentification $identifyAndTriggerAsyncIdentification
    ) {
        $merchantDebtor = (new MerchantDebtorEntity())->setId(1);
        $orderContainer->getMerchantDebtor()->willReturn($merchantDebtor);

        $orderChecksRunnerService->passesPreIdentificationChecks($orderContainer)->shouldBeCalledOnce()->willReturn(true);
        $identifyAndTriggerAsyncIdentification->identifyDebtor($orderContainer)->shouldBeCalledOnce()->willReturn(true);
        $orderChecksRunnerService->passesPostIdentificationChecks($orderContainer)->shouldBeCalledOnce()->willReturn(true);
        $orderChecksRunnerService->hasFailedSoftDeclinableChecks($orderContainer)->shouldBeCalledOnce()->willReturn(false);

        $order->isDeclined()->shouldBeCalledOnce()->willReturn(false);
        $approveOrderService->approve($orderContainer)->shouldBeCalledOnce();
        $declineOrderService->decline($orderContainer)->shouldNotBeCalled();
        $orderResponseFactory->createV1($orderContainer)->shouldBeCalledOnce()->willReturn(new OrderResponseV1());

        $this->execute($request);
    }

    private function mockRequest(CreateOrderRequest $request)
    {
        $request->getDuration()->willReturn(30);
        $request->getAmount()->willReturn(TaxedMoneyFactory::create(123, 123, 0));
        $request->getMerchantId()->willReturn(30);
        $request->getCheckoutSessionId()->willReturn(1);
        $request->getExternalCode()->willReturn('aaa123');
        $request->getComment()->willReturn('test');
        $request->getCheckoutSessionId()->willReturn(null);
    }
}
