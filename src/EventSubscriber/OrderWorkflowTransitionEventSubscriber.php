<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use Ozean12\Transfer\Message\Order\OrderCreated;
use Ozean12\Transfer\Shared\BuyerContactInfo;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Event\Event;

class OrderWorkflowTransitionEventSubscriber implements EventSubscriberInterface
{
    private OrderContainerFactory $orderContainerFactory;

    private MessageBusInterface $messageBus;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        MessageBusInterface $messageBus
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.order_v1.completed.create' => ['onCreate'],
            'workflow.order_v2.completed.create' => ['onCreate'],
        ];
    }

    public function onCreate(Event $event): void
    {
        /** @var OrderEntity $order */
        $order = $event->getSubject();
        $orderContainer = $this->getOrderContainer($order);

        $this->dispatchOrderCreated($orderContainer);
    }

    private function getOrderContainer(OrderEntity $orderEntity): OrderContainer
    {
        return $this->orderContainerFactory->getCachedOrderContainer() ?? $this->orderContainerFactory->createFromOrderEntity($orderEntity);
    }

    private function dispatchOrderCreated(OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();
        $merchant = $orderContainer->getMerchant();
        $debtor = $orderContainer->getMerchantDebtor();
        $debtorPerson = $orderContainer->getDebtorPerson();

        $message = (new OrderCreated())
            ->setUuid($order->getUuid())
            ->setDebtorCompanyUuid($debtor->getCompanyUuid())
            ->setDebtorPaymentUuid($debtor->getPaymentDebtorId())
            ->setMerchantCompanyUuid($merchant->getCompanyUuid())
            ->setMerchantPaymentUuid($merchant->getPaymentUuid())
            ->setBuyer(
                (new BuyerContactInfo())
                    ->setEmail($debtorPerson->getEmail())
                    ->setFirstName($debtorPerson->getFirstName())
                    ->setLastName($debtorPerson->getLastName())
                    ->setGender($debtorPerson->getGender())
            );

        $this->messageBus->dispatch($message);
    }
}
