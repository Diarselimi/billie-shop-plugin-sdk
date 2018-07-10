<?php

namespace App\Application\UseCase\OrderOutstandingAmountChange;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Merchant\MerchantNotFoundException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Webhook\NotificationDTO;
use App\DomainModel\Webhook\NotificationSender;

class OrderOutstandingAmountChangeUseCase
{
    private $orderRepository;
    private $merchantRepository;
    private $notificationSender;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        NotificationSender $notificationSender
    ) {
        $this->orderRepository = $orderRepository;
        $this->merchantRepository = $merchantRepository;
        $this->notificationSender = $notificationSender;
    }

    public function execute(OrderOutstandingAmountChangeRequest $request)
    {
        $orderAmountChangeDetails = $request->getOrderAmountChangeDetails();
        $order = $this->orderRepository->getOneByPaymentId($orderAmountChangeDetails->getId());

        if (!$order) {
            throw new PaellaCoreCriticalException('Order not found', PaellaCoreCriticalException::CODE_NOT_FOUND);
        }

        $merchant = $this->merchantRepository->getOneById($order->getMerchantId());
        if (is_null($merchant)) {
            throw new MerchantNotFoundException();
        }

        $merchant->increaseAvailableFinancingLimit($orderAmountChangeDetails->getAmountChange());
        $this->merchantRepository->update($merchant);

        if (!$orderAmountChangeDetails->isPayment()) {
            return;
        }

        $notification = (new NotificationDTO())
            ->setEventName(NotificationDTO::EVENT_PAYMENT)
            ->setOrderId($order->getExternalCode())
            ->setAmount($orderAmountChangeDetails->getPaidAmount())
            ->setOpenAmount($orderAmountChangeDetails->getOutstandingAmount())
        ;

        $this->notificationSender->send($merchant, $notification);
    }
}
