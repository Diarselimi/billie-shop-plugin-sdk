<?php

namespace spec\App\Application\UseCase\CreateOrder;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\Application\UseCase\CreateOrder\CreateOrderUseCase;
use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantDebtor\Finder\MerchantDebtorFinder;
use App\DomainModel\MerchantDebtor\Finder\MerchantDebtorFinderResult;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\Order\NewOrder\OrderCreationDTO;
use App\DomainModel\Order\NewOrder\OrderPersistenceService;
use App\Application\UseCase\CreateOrder\Request\CreateOrderAmountRequest;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
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
        MerchantDebtorFinder $debtorFinderService,
        ProducerInterface $producer,
        OrderResponseFactory $orderResponseFactory,
        OrderStateManager $orderStateManager,
        CheckoutSessionRepositoryInterface $checkoutSessionRepository,
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
        OrderStateManager $orderStateManager,
        OrderContainer $orderContainer,
        CreateOrderRequest $request
    ) {
        $orderChecksRunnerService->runPreIdentificationChecks($orderContainer)->willReturn(false);
        $orderStateManager->decline($orderContainer)->shouldBeCalledOnce();

        $this->shouldNotThrow(\Exception::class)->during('execute', [$request]);
    }

    public function it_should_be_declined_if_post_identifications_checks_fail(
        OrderChecksRunnerService $orderChecksRunnerService,
        MerchantDebtorFinder $debtorFinder,
        OrderStateManager $orderStateManager,
        OrderContainer $orderContainer,
        CreateOrderRequest $request,
        MerchantDebtorFinderResult $result
    ) {
        $orderChecksRunnerService->runPreIdentificationChecks($orderContainer)->willReturn(false);

        $result->getMerchantDebtor()->willReturn(new MerchantDebtorEntity());
        $result->getDebtorCompany()->willReturn(new DebtorCompany());
        $debtorFinder->findDebtor($orderContainer)->willReturn($result);

        $orderChecksRunnerService->runPostIdentificationChecks($orderContainer)->willReturn(false);

        $orderStateManager->decline($orderContainer)->shouldBeCalledOnce();

        $this->shouldNotThrow(\Exception::class)->during('execute', [$request]);
    }

    private function mockRequest(CreateOrderRequest $request)
    {
        $request->getDuration()->willReturn(30);
        $request->getAmount()->willReturn(
            (new CreateOrderAmountRequest())
            ->setNet(123.3)
            ->setTax(123.33)
            ->setGross(22.22)
        );
        $request->getMerchantId()->willReturn(30);
        $request->getCheckoutSessionId()->willReturn(1);
        $request->getExternalCode()->willReturn('aaa123');
        $request->getComment()->willReturn('test');
        $request->getCheckoutSessionId()->willReturn(null);
    }
}
