<?php

namespace spec\App\Application\UseCase\CheckoutAuthorizeOrder;

use App\Application\UseCase\CheckoutAuthorizeOrder\CheckoutAuthorizeOrderUseCase;
use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use App\DomainModel\Order\IdentifyAndTriggerAsyncIdentification;
use App\DomainModel\Order\Lifecycle\ApproveOrderService;
use App\DomainModel\Order\Lifecycle\DeclineOrderService;
use App\DomainModel\Order\NewOrder\OrderCreationDTO;
use App\DomainModel\Order\NewOrder\OrderPersistenceService;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Workflow\Registry;

class CheckoutAuthorizeOrderUseCaseSpec extends ObjectBehavior
{
    public function let(
        OrderPersistenceService $persistNewOrderService,
        OrderContainerFactory $orderContainerFactory,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderRepositoryInterface $orderRepository,
        Registry $workflowRegistry,
        ApproveOrderService $approveOrderService,
        DeclineOrderService $declineOrderService,
        CheckoutSessionRepositoryInterface $checkoutSessionRepository,
        IdentifyAndTriggerAsyncIdentification $identifyAndTriggerAsyncIdentification,
        OrderResponseFactory $orderResponseFactory,
        ValidatorInterface $validator,
        OrderCreationDTO $creationDTO,
        OrderContainer $orderContainer
    ) {
        $this->beConstructedWith(...func_get_args());

        $persistNewOrderService->persistFromRequest(Argument::any())->willReturn($creationDTO);
        $orderContainerFactory->createFromNewOrderDTO(Argument::any())->willReturn($orderContainer);

        $this->setValidator($validator);
        $validator->validate(Argument::any(), Argument::any(), Argument::any())->willReturn(new ConstraintViolationList());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CheckoutAuthorizeOrderUseCase::class);
    }
}
