<?php

namespace App\Application\UseCase\DeclineOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\LimitsService;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderNotification\NotificationScheduler;
use App\DomainModel\OrderRiskCheck\Checker\LimitCheck;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckRepositoryInterface;
use Symfony\Component\Workflow\Workflow;

class DeclineOrderUseCase
{
    const NOTIFICATION_EVENT = 'order_declined';

    private $orderRepository;

    private $orderRiskCheckRepository;

    private $merchantDebtorRepository;

    private $limitsService;

    private $notificationScheduler;

    private $workflow;

    private $orderStateManager;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderRiskCheckRepositoryInterface $orderRiskCheckRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        LimitsService $limitsService,
        notificationScheduler $notificationScheduler,
        Workflow $workflow,
        OrderStateManager $orderStateManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderRiskCheckRepository = $orderRiskCheckRepository;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->limitsService = $limitsService;
        $this->notificationScheduler = $notificationScheduler;
        $this->workflow = $workflow;
        $this->orderStateManager = $orderStateManager;
    }

    public function execute(DeclineOrderRequest $request): void
    {
        $order = $this->orderRepository->getOneByExternalCode($request->getOrderExternalCode(), $request->getMerchantId());

        if (!$order) {
            throw new OrderNotFoundException();
        }

        if (!$this->orderStateManager->isWaiting($order)) {
            throw new OrderWorkflowException("Cannot decline the order. Order is not in waiting state.");
        }

        $this->workflow->apply($order, OrderStateManager::TRANSITION_DECLINE);
        $this->orderRepository->update($order);

        $this->unlockLimitIfLocked($order);

        $this->notificationScheduler->createAndSchedule($order, [
            'event' => self::NOTIFICATION_EVENT,
            'order_id' => $order->getExternalCode(),
        ]);
    }

    private function unlockLimitIfLocked(OrderEntity $order): void
    {
        $limitCheckResult = $this->orderRiskCheckRepository->findByOrderAndCheckName($order->getId(), LimitCheck::NAME);

        if (!$limitCheckResult || !$limitCheckResult->isPassed()) {
            return;
        }

        $merchantDebtor = $this->merchantDebtorRepository->getOneById($order->getMerchantDebtorId());

        $this->limitsService->unlock($merchantDebtor, $order->getAmountGross());
    }
}
