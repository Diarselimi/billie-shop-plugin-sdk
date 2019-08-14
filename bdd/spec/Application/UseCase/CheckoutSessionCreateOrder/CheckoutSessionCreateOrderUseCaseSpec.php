<?php

namespace spec\App\Application\UseCase\CheckoutSessionCreateOrder;

use App\Application\UseCase\CheckoutSessionCreateOrder\CheckoutSessionCreateOrderUseCase;
use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use App\DomainModel\Order\IdentifyAndTriggerAsyncIdentification;
use App\DomainModel\Order\NewOrder\OrderCreationDTO;
use App\DomainModel\Order\NewOrder\OrderPersistenceService;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CheckoutSessionCreateOrderUseCaseSpec extends ObjectBehavior
{
    private $orderContainer;

    public function let(
        OrderPersistenceService $persistNewOrderService,
        OrderContainerFactory $orderContainerFactory,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        CheckoutSessionRepositoryInterface $checkoutSessionRepository,
        IdentifyAndTriggerAsyncIdentification $identifyAndTriggerAsyncIdentification,
        ValidatorInterface $validator,
        OrderCreationDTO $creationDTO,
        OrderContainer $orderContainer
    ) {
        $this->beConstructedWith(...func_get_args());

        $this->orderContainer = $orderContainer;
        $persistNewOrderService->persistFromRequest(Argument::any())->willReturn($creationDTO);
        $orderContainerFactory->createFromNewOrderDTO(Argument::any())->willReturn($orderContainer);

        $this->setValidator($validator);
        $validator->validate(Argument::any(), Argument::any(), Argument::any())->willReturn(new ConstraintViolationList());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CheckoutSessionCreateOrderUseCase::class);
    }
}
