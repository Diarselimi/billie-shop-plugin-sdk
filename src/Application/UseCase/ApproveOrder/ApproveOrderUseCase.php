<?php

namespace App\Application\UseCase\ApproveOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\DomainEvent\Order\OrderApprovedEvent;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\Workflow;

class ApproveOrderUseCase
{
    const NOTIFICATION_EVENT = 'order_approved';

    private $orderRepository;

    private $orderPersistenceService;

    private $workflow;

    private $orderStateManager;

    private $orderChecksRunnerService;

    private $declinedReasonsMapper;

    private $eventDispatcher;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        Workflow $workflow,
        OrderStateManager $orderStateManager,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderDeclinedReasonsMapper $declinedReasonsMapper,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderPersistenceService = $orderPersistenceService;
        $this->workflow = $workflow;
        $this->orderStateManager = $orderStateManager;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->declinedReasonsMapper = $declinedReasonsMapper;
        $this->eventDispatcher = $eventDispatcher;
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

        $this->eventDispatcher->dispatch(OrderApprovedEvent::NAME, new OrderApprovedEvent($orderContainer, true));
    }
}
