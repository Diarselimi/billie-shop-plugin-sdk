<?php

namespace App\DomainEvent\Order;

use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderNotification\NotificationScheduler;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderApprovedEventSubscriber implements EventSubscriberInterface, LoggingInterface
{
    use LoggingTrait;

    private const NOTIFICATION_EVENT = 'order_approved';

    private $merchantRepository;

    private $orderRepository;

    private $notificationScheduler;

    public function __construct(
        MerchantRepositoryInterface $merchantRepository,
        OrderRepositoryInterface $orderRepository,
        NotificationScheduler $notificationScheduler
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->orderRepository = $orderRepository;
        $this->notificationScheduler = $notificationScheduler;
    }

    public static function getSubscribedEvents()
    {
        return [
            OrderApprovedEvent::NAME => 'onOrderApproved',
        ];
    }

    public function onOrderApproved(OrderApprovedEvent $event): void
    {
        $this->reduceMerchantAvailableFinancingLimit($event->getOrderContainer());

        if ($event->isNotifyWebhook()) {
            $this->notifyMerchantWebhook($event->getOrderContainer()->getOrder());
        }

        $this->log($event->getOrderContainer());
    }

    private function reduceMerchantAvailableFinancingLimit(OrderContainer $orderContainer)
    {
        $merchant = $orderContainer->getMerchant();
        $merchant->reduceAvailableFinancingLimit($orderContainer->getOrder()->getAmountGross());
        $this->merchantRepository->update($merchant);
    }

    private function notifyMerchantWebhook(OrderEntity $order): void
    {
        $this->notificationScheduler->createAndSchedule($order, [
            'event' => self::NOTIFICATION_EVENT,
            'order_id' => $order->getExternalCode(),
        ]);
    }

    private function log(OrderContainer $orderContainer): void
    {
        $merchantDebtor = $orderContainer->getMerchantDebtor();
        $firstApprovedOrder = $this->orderRepository->merchantDebtorHasAtLeastOneApprovedOrder($merchantDebtor->getId());

        $this->logInfo("Order approved!", [
            'debtor_is_new' => !$firstApprovedOrder,
            'debtor_created_in_this_hour' => $merchantDebtor->getCreatedAt() > new \Datetime(date('Y-m-d H:00:00')),
            'debtor_created_today' => $merchantDebtor->getCreatedAt() > new \Datetime(date('Y-m-d 00:00:00')),
        ]);
    }
}
