<?php

namespace App\Application\UseCase\ApproveOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderNotification\NotificationScheduler;
use Symfony\Component\Workflow\Workflow;

class ApproveOrderUseCase
{
    const NOTIFICATION_EVENT = 'order_approved';

    private $orderRepository;

    private $merchantDebtorRepository;

    private $orderPersistenceService;

    private $notificationScheduler;

    private $workflow;

    private $orderStateManager;

    private $orderChecksRunnerService;

    private $declinedReasonsMapper;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        OrderPersistenceService $orderPersistenceService,
        notificationScheduler $notificationScheduler,
        Workflow $workflow,
        OrderStateManager $orderStateManager,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderDeclinedReasonsMapper $declinedReasonsMapper
    ) {
        $this->orderRepository = $orderRepository;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->orderPersistenceService = $orderPersistenceService;
        $this->notificationScheduler = $notificationScheduler;
        $this->workflow = $workflow;
        $this->orderStateManager = $orderStateManager;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->declinedReasonsMapper = $declinedReasonsMapper;
    }

    public function execute(ApproveOrderRequest $request): void
    {
        $order = $this->orderRepository->getOneByExternalCode($request->getOrderExternalCode(), $request->getMerchantId());

        if (!$order) {
            throw new OrderNotFoundException();
        }

        if (!$this->orderStateManager->isWaiting($order)) {
            throw new OrderWorkflowException("Cannot approve the order. Order is not in waiting state.");
        }

        $orderContainer = $this->orderPersistenceService->createFromOrderEntity($order);

        if (!$this->orderChecksRunnerService->rerunFailedChecks($orderContainer)) {
            throw new OrderWorkflowException(
                sprintf(
                    "Cannot approve the order. failed risk checks: %s",
                    implode(', ', $this->declinedReasonsMapper->mapReasons($order))
                )
            );
        }

        $this->workflow->apply($order, OrderStateManager::TRANSITION_CREATE);
        $this->orderRepository->update($order);

        $this->notificationScheduler->createAndSchedule($order, [
            'event' => self::NOTIFICATION_EVENT,
            'order_id' => $order->getExternalCode(),
        ]);
    }
}
