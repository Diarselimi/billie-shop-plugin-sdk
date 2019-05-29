<?php

namespace spec\App\Application\UseCase\CheckoutSessionConfirmOrder;

use App\Application\Exception\CheckoutSessionConfirmException;
use App\Application\UseCase\CheckoutSessionConfirmOrder\CheckoutSessionConfirmOrderRequest;
use App\Application\UseCase\CheckoutSessionConfirmOrder\CheckoutSessionConfirmUseCase;
use App\Application\UseCase\CreateOrder\Request\CreateOrderAmountRequest;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CheckoutSessionConfirmUseCaseSpec extends ObjectBehavior
{
    private $orderRepository;

    private $orderPersistenceService;

    private $orderResponseFactory;

    public function let(
        OrderRepositoryInterface $orderRepository,
        OrderResponseFactory $orderResponseFactory,
        OrderPersistenceService $orderPersistenceService,
        OrderStateManager $orderStateManager,
        ValidatorInterface $validator
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderPersistenceService = $orderPersistenceService;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->beConstructedWith(...func_get_args());
        $this->setValidator($validator);
        $validator->validate(Argument::any(), Argument::any(), Argument::any())->willReturn(new ConstraintViolationList());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CheckoutSessionConfirmUseCase::class);
    }

    public function it_should_throw_an_exception_if_duration_does_not_match()
    {
        $request = (new CheckoutSessionConfirmOrderRequest())
            ->setDuration(123)
            ->setAmount((new CreateOrderAmountRequest())->setGross(123.1)->setNet(123.1)->setTax(123.1))
        ;
        $checkoutSessionUuid = 'test123';

        $orderEntity = $this->getOrderEntityMock($request);
        $request->setDuration(111);
        $this
            ->orderRepository
            ->getOneByCheckoutSessionUuid($checkoutSessionUuid)
            ->willReturn($orderEntity);

        $orderContainer = new OrderContainer();
        $this->orderPersistenceService->createFromOrderEntity($orderEntity)->willReturn($orderContainer);

        $this->shouldThrow(CheckoutSessionConfirmException::class)->during('execute', [$request, $checkoutSessionUuid]);
    }

    public function it_should_throw_an_exception_if_gross_does_not_match()
    {
        $request = (new CheckoutSessionConfirmOrderRequest())
            ->setDuration(123)
            ->setAmount((new CreateOrderAmountRequest())->setGross(123.1)->setNet(123.1)->setTax(123.1))
        ;
        $checkoutSessionUuid = 'test123';

        $orderEntity = $this->getOrderEntityMock($request);
        $request->getAmount()->setGross(111.1);
        $this
            ->orderRepository
            ->getOneByCheckoutSessionUuid($checkoutSessionUuid)
            ->willReturn($orderEntity);

        $orderContainer = new OrderContainer();
        $this->orderPersistenceService->createFromOrderEntity($orderEntity)->willReturn($orderContainer);

        $this->shouldThrow(CheckoutSessionConfirmException::class)->during('execute', [$request, $checkoutSessionUuid]);
    }

    public function it_should_throw_an_exception_if_tax_does_not_match()
    {
        $request = (new CheckoutSessionConfirmOrderRequest())
            ->setDuration(123)
            ->setAmount((new CreateOrderAmountRequest())->setGross(123.1)->setNet(123.1)->setTax(123.1))
        ;
        $checkoutSessionUuid = 'test123';

        $orderEntity = $this->getOrderEntityMock($request);
        $request->getAmount()->setTax(111.1);
        $this
            ->orderRepository
            ->getOneByCheckoutSessionUuid($checkoutSessionUuid)
            ->willReturn($orderEntity);

        $orderContainer = new OrderContainer();
        $this->orderPersistenceService->createFromOrderEntity($orderEntity)->willReturn($orderContainer);

        $this->shouldThrow(CheckoutSessionConfirmException::class)->during('execute', [$request, $checkoutSessionUuid]);
    }

    public function it_should_throw_an_exception_if_net_does_not_match()
    {
        $request = (new CheckoutSessionConfirmOrderRequest())
            ->setDuration(123)
            ->setAmount((new CreateOrderAmountRequest())->setGross(123.1)->setNet(123.1)->setTax(123.1))
        ;
        $checkoutSessionUuid = 'test123';

        $orderEntity = $this->getOrderEntityMock($request);
        $request->getAmount()->setNet(111.1);
        $this
            ->orderRepository
            ->getOneByCheckoutSessionUuid($checkoutSessionUuid)
            ->willReturn($orderEntity);

        $orderContainer = new OrderContainer();
        $this->orderPersistenceService->createFromOrderEntity($orderEntity)->willReturn($orderContainer);

        $this->shouldThrow(CheckoutSessionConfirmException::class)->during('execute', [$request, $checkoutSessionUuid]);
    }

    public function it_should_create_order_if_everything_matches()
    {
        $request = (new CheckoutSessionConfirmOrderRequest())
            ->setDuration(123)
            ->setAmount((new CreateOrderAmountRequest())->setGross(123.1)->setNet(123.1)->setTax(123.1))
        ;
        $checkoutSessionUuid = 'test123';

        $orderEntity = $this->getOrderEntityMock($request);

        $this
            ->orderRepository
            ->getOneByCheckoutSessionUuid($checkoutSessionUuid)
            ->willReturn($orderEntity);

        $orderContainer = new OrderContainer();
        $this->orderPersistenceService->createFromOrderEntity($orderEntity)->willReturn($orderContainer);

        $this->orderResponseFactory->create($orderContainer)->willReturn(new OrderResponse());
        $this->orderResponseFactory->create($orderContainer)->shouldBeCalled();
        $this->shouldNotThrow(\Exception::class)->during('execute', [$request, $checkoutSessionUuid]);
    }

    private function getOrderEntityMock(CheckoutSessionConfirmOrderRequest $request)
    {
        return (new OrderEntity())
            ->setState(OrderStateManager::STATE_AUTHORIZED)
            ->setDuration($request->getDuration())
            ->setAmountNet($request->getAmount()->getNet())
            ->setAmountTax($request->getAmount()->getTax())
            ->setAmountGross($request->getAmount()->getGross());
    }
}
