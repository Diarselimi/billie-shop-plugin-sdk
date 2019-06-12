<?php

namespace App\DomainModel\Order;

use App\Application\Exception\OrderWorkflowException;
use App\DomainEvent\Order\OrderCompleteEvent;
use App\DomainEvent\Order\OrderApprovedEvent;
use App\DomainEvent\Order\OrderDeclinedEvent;
use App\DomainEvent\Order\OrderInWaitingStateEvent;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\Checker\LimitCheck;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\Workflow;

class OrderStateManager implements LoggingInterface
{
    use LoggingTrait;

    public const STATE_NEW = 'new';

    public const STATE_AUTHORIZED = 'authorized';

    public const STATE_WAITING = 'waiting';

    public const STATE_CREATED = 'created';

    public const STATE_DECLINED = 'declined';

    public const STATE_SHIPPED = 'shipped';

    public const STATE_PAID_OUT = 'paid_out';

    public const STATE_LATE = 'late';

    public const STATE_COMPLETE = 'complete';

    public const STATE_CANCELED = 'canceled';

    public const ALL_STATES = [
        self::STATE_NEW,
        self::STATE_AUTHORIZED,
        self::STATE_WAITING,
        self::STATE_CREATED,
        self::STATE_DECLINED,
        self::STATE_SHIPPED,
        self::STATE_PAID_OUT,
        self::STATE_LATE,
        self::STATE_COMPLETE,
        self::STATE_CANCELED,
    ];

    public const TRANSITION_NEW = 'new';

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

    private $orderRepository;

    private $workflow;

    private $eventDispatcher;

    private $orderApproveCrossChecksService;

    private $orderChecksRunnerService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Workflow $workflow,
        CreateOrderCrossChecksService $approveCrossChecksService,
        EventDispatcherInterface $eventDispatcher,
        OrderChecksRunnerService $orderChecksRunnerService
    ) {
        $this->orderRepository = $orderRepository;
        $this->workflow = $workflow;
        $this->orderApproveCrossChecksService = $approveCrossChecksService;
        $this->eventDispatcher = $eventDispatcher;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
    }

    public function wasShipped(OrderEntity $order): bool
    {
        return in_array($order->getState(), [
            self::STATE_SHIPPED,
            self::STATE_PAID_OUT,
            self::STATE_LATE,
        ], true);
    }

    public function canConfirmPayment(OrderEntity $order): bool
    {
        return in_array($order->getState(), [
            self::STATE_SHIPPED,
            self::STATE_PAID_OUT,
            self::STATE_LATE,
        ], true);
    }

    public function isNew(OrderEntity $order): bool
    {
        return $order->getState() === self::STATE_NEW;
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

    public function isAuthorized(OrderEntity $order): bool
    {
        return $order->getState() === self::STATE_AUTHORIZED;
    }

    public function approve(OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();
        $shouldNotifyWebhook = $this->isWaiting($order);

        try {
            $this->orderApproveCrossChecksService->run($orderContainer);
        } catch (OrderWorkflowException $exception) {
            $this->logSuppressedException($exception, 'Order approve failed because of cross checks');

            throw new OrderWorkflowException('Order approve exception', null, $exception);
        }

        $this->workflow->apply($order, OrderStateManager::TRANSITION_CREATE);
        $this->orderRepository->update($order);

        $this->eventDispatcher->dispatch(OrderApprovedEvent::NAME, new OrderApprovedEvent($orderContainer, $shouldNotifyWebhook));
        $this->logInfo("Order approved");
    }

    public function decline(OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();
        $shouldNotifyWebhook = $this->isWaiting($order);

        $this->workflow->apply($order, OrderStateManager::TRANSITION_DECLINE);
        $this->orderRepository->update($order);

        $this->eventDispatcher->dispatch(OrderDeclinedEvent::NAME, new OrderDeclinedEvent($orderContainer, $shouldNotifyWebhook));
        $this->logInfo("Order declined");
    }

    public function wait(OrderContainer $orderContainer)
    {
        $order = $orderContainer->getOrder();
        $this->orderChecksRunnerService->invalidateRiskCheck($orderContainer, LimitCheck::NAME);

        $this->workflow->apply($order, OrderStateManager::TRANSITION_WAITING);
        $this->orderRepository->update($order);

        $this->eventDispatcher->dispatch(OrderInWaitingStateEvent::NAME, new OrderInWaitingStateEvent($orderContainer));
        $this->logInfo("Order was moved to waiting state");
    }

    public function authorize(OrderContainer $orderContainer)
    {
        $this->workflow->apply($orderContainer->getOrder(), OrderStateManager::TRANSITION_AUTHORIZE);
        $this->orderRepository->update($orderContainer->getOrder());
        $this->logInfo("Order was moved to authorized state");
    }

    public function complete(OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();

        $this->workflow->apply($order, OrderStateManager::TRANSITION_COMPLETE);
        $this->orderRepository->update($order);

        $this->eventDispatcher->dispatch(OrderCompleteEvent::NAME, new OrderCompleteEvent($orderContainer));

        $this->logInfo("Order completed");
    }
}
