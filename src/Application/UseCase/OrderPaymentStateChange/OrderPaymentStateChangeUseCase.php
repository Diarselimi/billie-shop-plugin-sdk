<?php

namespace App\Application\UseCase\OrderPaymentStateChange;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Borscht\OrderPaymentDetailsDTO;
use App\DomainModel\Merchant\MerchantNotFoundException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\Webhook\NotificationDTO;
use App\DomainModel\Webhook\NotificationSender;
use Symfony\Component\Workflow\Workflow;

class OrderPaymentStateChangeUseCase
{
    private $orderRepository;
    private $workflow;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        Workflow $workflow,
        NotificationSender $notificationSender
    ) {
        $this->orderRepository = $orderRepository;
        $this->merchantRepository = $merchantRepository;
        $this->workflow = $workflow;
        $this->notificationSender = $notificationSender;
    }

    public function execute(OrderPaymentStateChangeRequest $request)
    {
        $orderPaymentDetails = $request->getOrderPaymentDetails();
        $order = $this->orderRepository->getOneByPaymentId($orderPaymentDetails->getId());

        if (!$order) {
            throw new PaellaCoreCriticalException('Order not found', PaellaCoreCriticalException::CODE_NOT_FOUND);
        }

        if ($orderPaymentDetails->isLate()) {
            $this->workflow->apply($order, OrderStateManager::TRANSITION_LATE);
            $this->orderRepository->update($order);
        } elseif ($orderPaymentDetails->isPaidOut()) {
            $this->workflow->apply($order, OrderStateManager::TRANSITION_PAY_OUT);
            $this->orderRepository->update($order);

            $this->notify($order, $orderPaymentDetails);
        } elseif ($orderPaymentDetails->isPaidFully()) {
            $this->workflow->apply($order, OrderStateManager::STATE_COMPLETE);
            $this->orderRepository->update($order);

            $this->notify($order, $orderPaymentDetails);
        }
    }

    private function notify(OrderEntity $order, OrderPaymentDetailsDTO $orderPaymentDetails)
    {
        $merchant = $this->merchantRepository->getOneById($order->getMerchantId());
        if (is_null($merchant)) {
            throw new MerchantNotFoundException();
        }

        $notification = (new NotificationDTO())
            ->setEventName(NotificationSender::EVENT_PAYMENT)
            ->setOrderId($order->getId())
            ->setAmount($orderPaymentDetails->getPayoutAmount())
            ->setOpenAmount($orderPaymentDetails->getOutstandingAmount())
        ;

        $this->notificationSender->send($merchant, $notification);
    }
}
