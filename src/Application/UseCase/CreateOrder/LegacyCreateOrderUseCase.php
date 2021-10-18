<?php

namespace App\Application\UseCase\CreateOrder;

use App\Application\UseCase\OrderCreationUseCaseTrait;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\CompanyIdentifier;
use App\DomainModel\Order\Lifecycle\ApproveOrderService;
use App\DomainModel\Order\Lifecycle\DeclineOrderService;
use App\DomainModel\Order\Lifecycle\WaitingOrderService;
use App\DomainModel\Order\NewOrder\OrderPersistenceService;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderResponse\LegacyOrderResponse;
use App\DomainModel\OrderResponse\LegacyOrderResponseFactory;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class LegacyCreateOrderUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait, OrderCreationUseCaseTrait;

    private ApproveOrderService $approveOrderService;

    private WaitingOrderService $waitingOrderService;

    public function __construct(
        OrderPersistenceService $orderPersistenceService,
        OrderContainerFactory $orderContainerFactory,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderRepositoryInterface $orderRepository,
        LegacyOrderResponseFactory $orderResponseFactory,
        ApproveOrderService $approveOrderService,
        WaitingOrderService $waitingOrderService,
        DeclineOrderService $declineOrderService,
        CompanyIdentifier $companyIdentifier
    ) {
        $this->orderPersistenceService = $orderPersistenceService;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->orderRepository = $orderRepository;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->approveOrderService = $approveOrderService;
        $this->declineOrderService = $declineOrderService;
        $this->waitingOrderService = $waitingOrderService;
        $this->companyIdentifier = $companyIdentifier;
    }

    public function execute(CreateOrderRequestInterface $request): LegacyOrderResponse
    {
        $this->validateRequest($request);

        $orderContainer = $this->createIdentifiedOrder($request);
        $order = $orderContainer->getOrder();

        if ($order->isDeclined()) {
            return $this->orderResponseFactory->create($orderContainer);
        }

        if ($this->orderChecksRunnerService->hasFailedSoftDeclinableChecks($orderContainer)) {
            $this->waitingOrderService->wait($orderContainer);

            return $this->orderResponseFactory->create($orderContainer);
        }

        $this->approveOrderService->approve($orderContainer);

        return $this->orderResponseFactory->create($orderContainer);
    }
}
