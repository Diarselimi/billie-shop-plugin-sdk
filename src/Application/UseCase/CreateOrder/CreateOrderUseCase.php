<?php

namespace App\Application\UseCase\CreateOrder;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\IdentifyAndTriggerAsyncIdentification;
use App\DomainModel\Order\NewOrder\OrderPersistenceService;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class CreateOrderUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $persistNewOrderService;

    private $orderContainerFactory;

    private $orderChecksRunnerService;

    private $orderRepository;

    private $orderResponseFactory;

    private $orderStateManager;

    private $identifyAndTriggerAsyncIdentification;

    public function __construct(
        OrderPersistenceService $persistNewOrderService,
        OrderContainerFactory $orderContainerFactory,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderRepositoryInterface $orderRepository,
        OrderResponseFactory $orderResponseFactory,
        OrderStateManager $orderStateManager,
        IdentifyAndTriggerAsyncIdentification $identifyAndTriggerAsyncIdentification
    ) {
        $this->persistNewOrderService = $persistNewOrderService;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->orderRepository = $orderRepository;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->orderStateManager = $orderStateManager;
        $this->identifyAndTriggerAsyncIdentification = $identifyAndTriggerAsyncIdentification;
    }

    public function execute(CreateOrderRequest $request): OrderResponse
    {
        $this->validateRequest($request);
        $orderDTO = $this->persistNewOrderService->persistFromRequest($request);
        $orderContainer = $this->orderContainerFactory->createFromNewOrderDTO($orderDTO);

        if (!$this->orderChecksRunnerService->runPreIdentificationChecks($orderContainer)) {
            $this->orderStateManager->decline($orderContainer);

            return $this->orderResponseFactory->create($orderContainer);
        }

        if ($this->identifyAndTriggerAsyncIdentification->identifyDebtor($orderContainer)) {
            $this->orderRepository->update($orderContainer->getOrder());
        }

        if (!$this->orderChecksRunnerService->runPostIdentificationChecks($orderContainer)) {
            $this->orderStateManager->decline($orderContainer);

            return $this->orderResponseFactory->create($orderContainer);
        }

        if ($this->orderChecksRunnerService->checkForFailedSoftDeclinableCheckResults($orderContainer)) {
            $this->orderStateManager->wait($orderContainer);

            return $this->orderResponseFactory->create($orderContainer);
        }

        $this->orderStateManager->approve($orderContainer);

        return $this->orderResponseFactory->create($orderContainer);
    }
}
