<?php

namespace App\Application\UseCase\CheckoutSessionCreateOrder;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\Application\Exception\OrderDeclinedException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use App\DomainModel\Order\IdentifyAndTriggerAsyncIdentification;
use App\DomainModel\Order\NewOrder\OrderPersistenceService;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class CheckoutSessionCreateOrderUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $persistNewOrderService;

    private $orderContainerFactory;

    private $orderChecksRunnerService;

    private $orderRepository;

    private $orderResponseFactory;

    private $orderStateManager;

    private $checkoutSessionRepository;

    private $declinedReasonsMapper;

    private $identifyAndTriggerAsyncIdentification;

    public function __construct(
        OrderPersistenceService $persistNewOrderService,
        OrderContainerFactory $orderContainerFactory,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderRepositoryInterface $orderRepository,
        OrderResponseFactory $orderResponseFactory,
        OrderStateManager $orderStateManager,
        CheckoutSessionRepositoryInterface $checkoutSessionRepository,
        OrderDeclinedReasonsMapper $declinedReasonsMapper,
        IdentifyAndTriggerAsyncIdentification $identifyAndTriggerAsyncIdentification
    ) {
        $this->persistNewOrderService = $persistNewOrderService;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->orderRepository = $orderRepository;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->orderStateManager = $orderStateManager;
        $this->checkoutSessionRepository = $checkoutSessionRepository;
        $this->declinedReasonsMapper = $declinedReasonsMapper;
        $this->identifyAndTriggerAsyncIdentification = $identifyAndTriggerAsyncIdentification;
    }

    public function execute(CreateOrderRequest $request): OrderContainer
    {
        $this->validateRequest($request);

        $orderRequest = $this->persistNewOrderService->persistFromRequest($request);
        $orderContainer = $this->orderContainerFactory->createFromNewOrderDTO($orderRequest);

        if (!$this->orderChecksRunnerService->runPreIdentificationChecks($orderContainer)) {
            $this->orderStateManager->decline($orderContainer);

            throw new OrderDeclinedException($this->declinedReasonsMapper->mapReasons($orderContainer->getOrder()));
        }

        if ($this->identifyAndTriggerAsyncIdentification->identifyDebtor($orderContainer)) {
            $this->orderRepository->update($orderContainer->getOrder());
        }

        if (!$this->orderChecksRunnerService->runPostIdentificationChecks($orderContainer) ||
            $this->orderChecksRunnerService->checkForFailedSoftDeclinableCheckResults($orderContainer)) {
            $this->orderStateManager->decline($orderContainer);

            throw new OrderDeclinedException($this->declinedReasonsMapper->mapReasons($orderContainer->getOrder()));
        }

        $this->orderStateManager->authorize($orderContainer);
        $this->checkoutSessionRepository->invalidateById($request->getCheckoutSessionId());

        return $orderContainer;
    }
}
