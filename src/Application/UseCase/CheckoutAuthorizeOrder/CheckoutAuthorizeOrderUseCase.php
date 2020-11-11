<?php

namespace App\Application\UseCase\CheckoutAuthorizeOrder;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\Application\UseCase\OrderCreationUseCaseTrait;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use App\DomainModel\Order\IdentifyAndTriggerAsyncIdentification;
use App\DomainModel\Order\Lifecycle\ApproveOrderService;
use App\DomainModel\Order\Lifecycle\DeclineOrderService;
use App\DomainModel\Order\NewOrder\OrderPersistenceService;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderResponse\CheckoutAuthorizeOrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Registry;

class CheckoutAuthorizeOrderUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait, OrderCreationUseCaseTrait;

    private CheckoutSessionRepositoryInterface $checkoutSessionRepository;

    public function __construct(
        OrderPersistenceService $orderPersistenceService,
        OrderContainerFactory $orderContainerFactory,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderRepositoryInterface $orderRepository,
        Registry $workflowRegistry,
        ApproveOrderService $approveOrderService,
        DeclineOrderService $declineOrderService,
        CheckoutSessionRepositoryInterface $checkoutSessionRepository,
        IdentifyAndTriggerAsyncIdentification $identifyAndTriggerAsyncIdentification,
        OrderResponseFactory $orderResponseFactory
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

    public function execute(CreateOrderRequest $request): CheckoutAuthorizeOrderResponse
    {
        $this->validateRequest($request, null, ['Default', 'AuthorizeOrder']);

        $orderContainer = $this->createIdentifiedOrder($request);
        $order = $orderContainer->getOrder();

        if ($order->isDeclined()) {
            return $this->orderResponseFactory->createAuthorizeResponse($orderContainer);
        }

        if ($this->orderChecksRunnerService->hasFailedSoftDeclinableChecks($orderContainer)) {
            $this->workflowRegistry->get($order)->apply($order, OrderEntity::TRANSITION_PRE_WAITING);
            $this->checkoutSessionRepository->invalidateById($request->getCheckoutSessionId());

            return $this->orderResponseFactory->createAuthorizeResponse($orderContainer);
        }

        $this->workflowRegistry->get($order)->apply($order, OrderEntity::TRANSITION_AUTHORIZE);
        $this->checkoutSessionRepository->invalidateById($request->getCheckoutSessionId());

        return $this->orderResponseFactory->createAuthorizeResponse($orderContainer);
    }
}
