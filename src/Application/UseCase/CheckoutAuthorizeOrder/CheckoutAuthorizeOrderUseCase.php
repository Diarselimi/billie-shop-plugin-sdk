<?php

namespace App\Application\UseCase\CheckoutAuthorizeOrder;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\Application\UseCase\OrderCreationUseCaseTrait;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use App\DomainModel\Order\IdentifyAndTriggerAsyncIdentification;
use App\DomainModel\Order\NewOrder\OrderPersistenceService;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderResponse\CheckoutAuthorizeOrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class CheckoutAuthorizeOrderUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait, OrderCreationUseCaseTrait;

    private $checkoutSessionRepository;

    public function __construct(
        OrderPersistenceService $persistNewOrderService,
        OrderContainerFactory $orderContainerFactory,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        CheckoutSessionRepositoryInterface $checkoutSessionRepository,
        IdentifyAndTriggerAsyncIdentification $identifyAndTriggerAsyncIdentification,
        OrderResponseFactory $orderResponseFactory
    ) {
        $this->persistNewOrderService = $persistNewOrderService;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->orderRepository = $orderRepository;
        $this->orderStateManager = $orderStateManager;
        $this->checkoutSessionRepository = $checkoutSessionRepository;
        $this->identifyAndTriggerAsyncIdentification = $identifyAndTriggerAsyncIdentification;
        $this->orderResponseFactory = $orderResponseFactory;
    }

    public function execute(CreateOrderRequest $request): CheckoutAuthorizeOrderResponse
    {
        $this->validateRequest($request, null, ['Default', 'AuthorizeOrder']);

        $orderContainer = $this->createIdentifiedOrder($request);

        if ($this->orderStateManager->isDeclined($orderContainer->getOrder())) {
            return $this->orderResponseFactory->createAuthorizeResponse($orderContainer);
        }

        if ($this->orderChecksRunnerService->hasFailedSoftDeclinableChecks($orderContainer)) {
            $this->orderStateManager->preWait($orderContainer);
            $this->checkoutSessionRepository->invalidateById($request->getCheckoutSessionId());

            return $this->orderResponseFactory->createAuthorizeResponse($orderContainer);
        }

        $this->orderStateManager->authorize($orderContainer);
        $this->checkoutSessionRepository->invalidateById($request->getCheckoutSessionId());

        return $this->orderResponseFactory->createAuthorizeResponse($orderContainer);
    }
}
