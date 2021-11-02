<?php

namespace spec\App\Application\UseCase\AuthorizeOrder;

use App\DomainModel\Order\CompanyIdentifier;
use App\Application\UseCase\AuthorizeOrder\AuthorizeOrderHandler;
use App\DomainModel\CheckoutSession\CheckoutSessionRepository;
use App\DomainModel\Order\Lifecycle\DeclineOrderService;
use App\DomainModel\Order\NewOrder\OrderCreationDTO;
use App\DomainModel\Order\NewOrder\OrderPersistenceService;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderRepository;
use App\DomainModel\OrderResponse\LegacyOrderResponseFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Workflow\Registry;

class AuthorizeOrderHandlerSpec extends ObjectBehavior
{
    public function let(
        OrderPersistenceService $persistNewOrderService,
        OrderContainerFactory $orderContainerFactory,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderRepository $orderRepository,
        Registry $workflowRegistry,
        DeclineOrderService $declineOrderService,
        CompanyIdentifier $companyIdentifier,
        CheckoutSessionRepository $checkoutSessionRepository,
        LegacyOrderResponseFactory $orderResponseFactory,
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
        $this->shouldHaveType(AuthorizeOrderHandler::class);
    }
}
