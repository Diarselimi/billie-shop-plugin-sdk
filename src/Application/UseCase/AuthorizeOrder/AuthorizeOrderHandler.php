<?php

namespace App\Application\UseCase\AuthorizeOrder;

use App\Application\CommandHandler;
use App\Application\UseCase\CreateOrder\LegacyCreateOrderRequest;
use App\Application\UseCase\OrderCreationUseCaseTrait;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\CheckoutSession\CheckoutSessionRepository;
use App\DomainModel\Order\IdentifyAndTriggerAsyncIdentification;
use App\DomainModel\Order\Lifecycle\ApproveOrderService;
use App\DomainModel\Order\Lifecycle\DeclineOrderService;
use App\DomainModel\Order\NewOrder\OrderPersistenceService;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderResponse\LegacyOrderResponseFactory;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Registry;

class AuthorizeOrderHandler implements LoggingInterface, ValidatedUseCaseInterface, CommandHandler
{
    use LoggingTrait, ValidatedUseCaseTrait, OrderCreationUseCaseTrait;

    private CheckoutSessionRepository $checkoutSessionRepository;

    public function __construct(
        OrderPersistenceService $orderPersistenceService,
        OrderContainerFactory $orderContainerFactory,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderRepositoryInterface $orderRepository,
        Registry $workflowRegistry,
        ApproveOrderService $approveOrderService,
        DeclineOrderService $declineOrderService,
        CheckoutSessionRepository $checkoutSessionRepository,
        IdentifyAndTriggerAsyncIdentification $identifyAndTriggerAsyncIdentification,
        LegacyOrderResponseFactory $orderResponseFactory
    ) {
        $this->orderPersistenceService = $orderPersistenceService;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->orderRepository = $orderRepository;
        $this->workflowRegistry = $workflowRegistry;
        $this->approveOrderService = $approveOrderService;
        $this->declineOrderService = $declineOrderService;
        $this->checkoutSessionRepository = $checkoutSessionRepository;
        $this->identifyAndTriggerAsyncIdentification = $identifyAndTriggerAsyncIdentification;
        $this->orderResponseFactory = $orderResponseFactory;
    }

    public function execute(AuthorizeOrder $request): void
    {
        $this->validateRequest($request, null, ['Default', 'AuthorizeOrder']);

        $orderContainer = $this->createIdentifiedOrder($request);
        $order = $orderContainer->getOrder();

        if ($order->isDeclined()) {
            return;
        }

        $this->invalidateCheckoutSession($request);

        if ($this->orderChecksRunnerService->hasFailedSoftDeclinableChecks($orderContainer)) {
            $this->workflowRegistry->get($order)->apply($order, OrderEntity::TRANSITION_PRE_WAITING);

            return;
        }

        $this->workflowRegistry->get($order)->apply($order, OrderEntity::TRANSITION_AUTHORIZE);
    }

    private function invalidateCheckoutSession(LegacyCreateOrderRequest $request): void
    {
        $checkoutSession = $this->checkoutSessionRepository->findById($request->getCheckoutSessionId());

        $checkoutSession->deactivate();
        $this->checkoutSessionRepository->save($checkoutSession);
    }
}
