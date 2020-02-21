<?php

namespace App\DomainModel\Order;

use App\Application\Exception\WorkflowException;
use App\DomainEvent\Order\OrderAuthorizedEvent;
use App\DomainEvent\Order\OrderCanceledEvent;
use App\DomainEvent\Order\OrderCompleteEvent;
use App\DomainEvent\Order\OrderApprovedEvent;
use App\DomainEvent\Order\OrderDeclinedEvent;
use App\DomainEvent\Order\OrderInPreWaitingStateEvent;
use App\DomainEvent\Order\OrderInWaitingStateEvent;
use App\DomainEvent\Order\OrderIsLateEvent;
use App\DomainEvent\Order\OrderPaidOutEvent;
use App\DomainEvent\Order\OrderShippedEvent;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainEvent\Order\OrderPreApprovedEvent;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\Workflow;

class OrderStateManager implements LoggingInterface
{
    use LoggingTrait;

    public const STATE_NEW = 'new';

    public const STATE_PRE_WAITING = 'pre_waiting';

    public const STATE_AUTHORIZED = 'authorized';

    public const STATE_WAITING = 'waiting';

    public const STATE_CREATED = 'created';

    public const STATE_DECLINED = 'declined';

    public const STATE_SHIPPED = 'shipped';

    public const STATE_PAID_OUT = 'paid_out';

    public const STATE_LATE = 'late';

    public const STATE_COMPLETE = 'complete';

    public const STATE_CANCELED = 'canceled';

    public const STATE_PRE_APPROVED = 'pre_approved';

    public const ALL_STATES = [
        self::STATE_NEW,
        self::STATE_PRE_WAITING,
        self::STATE_AUTHORIZED,
        self::STATE_WAITING,
        self::STATE_CREATED,
        self::STATE_DECLINED,
        self::STATE_SHIPPED,
        self::STATE_PAID_OUT,
        self::STATE_LATE,
        self::STATE_COMPLETE,
        self::STATE_CANCELED,
        self::STATE_PRE_APPROVED,
    ];

    private const STATE_TRANSITION_EVENTS = [
        self::STATE_PRE_APPROVED => OrderPreApprovedEvent::class,
        self::STATE_WAITING => OrderInWaitingStateEvent::class,
        self::STATE_SHIPPED => OrderShippedEvent::class,
        self::STATE_PAID_OUT => OrderPaidOutEvent::class,
        self::STATE_LATE => OrderIsLateEvent::class,
        self::STATE_CANCELED => OrderCanceledEvent::class,
        self::STATE_COMPLETE => OrderCompleteEvent::class,
        self::STATE_PRE_WAITING => OrderInPreWaitingStateEvent::class,
        self::STATE_AUTHORIZED => OrderAuthorizedEvent::class,
    ];

    public const TRANSITION_PRE_WAITING = 'pre_waiting';

    public const TRANSITION_AUTHORIZE = 'authorize';

    public const TRANSITION_WAITING = 'waiting';

    public const TRANSITION_CREATE = 'create';

    public const TRANSITION_DECLINE = 'decline';

    public const TRANSITION_PAY_OUT = 'pay_out';

    public const TRANSITION_SHIP = 'ship';

    public const TRANSITION_LATE = 'late';

    public const TRANSITION_COMPLETE = 'complete';

    public const TRANSITION_CANCEL = 'cancel';

    public const TRANSITION_CANCEL_SHIPPED = 'cancel_shipped';

    public const TRANSITION_PRE_APPROVED = 'pre_approve';

    private $orderRepository;

    private $workflow;

    private $eventDispatcher;

    private $orderCrossChecksService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Workflow $orderWorkflow,
        CreateOrderCrossChecksService $orderCrossChecksService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->orderRepository = $orderRepository;
        $this->workflow = $orderWorkflow;
        $this->orderCrossChecksService = $orderCrossChecksService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function can(OrderEntity $order, string $transitionName): bool
    {
        return $this->workflow->can($order, $transitionName);
    }

    public function wasShipped(OrderEntity $order): bool
    {
        return in_array($order->getState(), [
            self::STATE_SHIPPED,
            self::STATE_PAID_OUT,
            self::STATE_LATE,
        ], true);
    }

    public function isCreated(OrderEntity $order): bool
    {
        return $order->getState() === self::STATE_CREATED;
    }

    public function isPreWaiting(OrderEntity $order): bool
    {
        return $order->getState() === self::STATE_PRE_WAITING;
    }

    public function isLate(OrderEntity $order): bool
    {
        return $order->getState() === self::STATE_LATE;
    }

    public function isDeclined(OrderEntity $order): bool
    {
        return $order->getState() === self::STATE_DECLINED;
    }

    public function isComplete(OrderEntity $order): bool
    {
        return $order->getState() === self::STATE_COMPLETE;
    }

    public function isCanceled(OrderEntity $order): bool
    {
        return $order->getState() === self::STATE_CANCELED;
    }

    public function isPaidOut(OrderEntity $order): bool
    {
        return $order->getState() === self::STATE_PAID_OUT;
    }

    public function isWaiting(OrderEntity $order): bool
    {
        return $order->getState() === self::STATE_WAITING;
    }

    public function isPreApproved(OrderEntity $order): bool
    {
        return $order->getState() === self::STATE_PRE_APPROVED;
    }

    public function approve(OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();
        $shouldNotifyWebhook = $this->isWaiting($order);

        try {
            $this->orderCrossChecksService->run($orderContainer);
        } catch (WorkflowException $exception) {
            $this->logSuppressedException($exception, 'Order approve failed because of cross checks');

            throw new WorkflowException('Order cannot be approved', null, $exception);
        }

        $this->workflow->apply($order, OrderStateManager::TRANSITION_CREATE);
        $this->orderRepository->update($order);

        $this->eventDispatcher->dispatch(new OrderApprovedEvent($orderContainer, $shouldNotifyWebhook));
        $this->logInfo("Order approved");
    }

    public function decline(OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();
        $shouldNotifyWebhook = $this->isWaiting($order);

        $this->workflow->apply($order, OrderStateManager::TRANSITION_DECLINE);
        $this->orderRepository->update($order);

        $this->eventDispatcher->dispatch(new OrderDeclinedEvent($orderContainer, $shouldNotifyWebhook));
        $this->logInfo("Order declined");
    }

    public function wait(OrderContainer $orderContainer)
    {
        $this->workflow->apply($orderContainer->getOrder(), OrderStateManager::TRANSITION_WAITING);
        $this->update($orderContainer);
    }

    public function preWait(OrderContainer $orderContainer)
    {
        $this->workflow->apply($orderContainer->getOrder(), OrderStateManager::TRANSITION_PRE_WAITING);
        $this->update($orderContainer);
    }

    public function authorize(OrderContainer $orderContainer)
    {
        $this->workflow->apply($orderContainer->getOrder(), OrderStateManager::TRANSITION_AUTHORIZE);
        $this->update($orderContainer);
    }

    public function preApprove(OrderContainer $orderContainer)
    {
        $this->workflow->apply($orderContainer->getOrder(), OrderStateManager::TRANSITION_PRE_APPROVED);
        $this->update($orderContainer);
    }

    public function complete(OrderContainer $orderContainer): void
    {
        $this->workflow->apply($orderContainer->getOrder(), OrderStateManager::TRANSITION_COMPLETE);
        $this->update($orderContainer);
    }

    public function payOut(OrderContainer $orderContainer): void
    {
        $this->workflow->apply($orderContainer->getOrder(), OrderStateManager::TRANSITION_PAY_OUT);
        $this->update($orderContainer);
    }

    public function ship(OrderContainer $orderContainer): void
    {
        $this->workflow->apply($orderContainer->getOrder(), OrderStateManager::TRANSITION_SHIP);
        $this->update($orderContainer);
    }

    public function late(OrderContainer $orderContainer): void
    {
        $this->workflow->apply($orderContainer->getOrder(), OrderStateManager::TRANSITION_LATE);

        $this->update($orderContainer);
    }

    public function cancel(OrderContainer $orderContainer): void
    {
        $this->workflow->apply($orderContainer->getOrder(), OrderStateManager::TRANSITION_CANCEL);
        $this->update($orderContainer);
    }

    public function cancelShipped(OrderContainer $orderContainer): void
    {
        $this->workflow->apply($orderContainer->getOrder(), OrderStateManager::TRANSITION_CANCEL_SHIPPED);
        $this->update($orderContainer);
    }

    private function update(OrderContainer $orderContainer): void
    {
        $this->orderRepository->update($orderContainer->getOrder());

        $state = $orderContainer->getOrder()->getState();

        if (isset(self::STATE_TRANSITION_EVENTS[$state])) {
            $eventClass = self::STATE_TRANSITION_EVENTS[$state];
            $this->eventDispatcher->dispatch(new $eventClass($orderContainer));
        }

        $this->logInfo(sprintf('Order was moved to %s state', $state));
    }
}
