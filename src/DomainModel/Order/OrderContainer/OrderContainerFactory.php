<?php

namespace App\DomainModel\Order\OrderContainer;

use App\DomainModel\Order\NewOrder\OrderCreationDTO;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderRiskCheck\CheckResultCollection;

class OrderContainerFactory
{
    private $orderRepository;

    private $relationLoader;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderContainerRelationLoader $relationLoader
    ) {
        $this->orderRepository = $orderRepository;
        $this->relationLoader = $relationLoader;
    }

    public function loadById(int $orderId): OrderContainer
    {
        $order = $this->orderRepository->getOneById($orderId);
        if ($order === null) {
            throw new OrderContainerFactoryException("Order not found");
        }

        return new OrderContainer($order, $this->relationLoader);
    }

    public function createFromOrderEntity(OrderEntity $order): OrderContainer
    {
        return new OrderContainer($order, $this->relationLoader);
    }

    public function createFromPaymentId(string $paymentId): OrderContainer
    {
        $order = $this->orderRepository->getOneByPaymentId($paymentId);
        if (!$order) {
            throw new OrderContainerFactoryException("Order not found");
        }

        return new OrderContainer($order, $this->relationLoader);
    }

    public function createFromInvoiceId(string $paymentId): OrderContainer
    {
        $order = $this->orderRepository->getByInvoice($paymentId)[0];
        if (!$order) {
            throw new OrderContainerFactoryException("Order not found");
        }

        return new OrderContainer($order, $this->relationLoader);
    }

    public function loadByUuid(string $uuid): OrderContainer
    {
        $order = $this->orderRepository->getOneByUuid($uuid);
        if (!$order) {
            throw new OrderContainerFactoryException("Order not found");
        }

        return new OrderContainer($order, $this->relationLoader);
    }

    public function loadByInvoiceUuid(string $uuid): OrderContainer
    {
        $orders = $this->orderRepository->getByInvoice($uuid);
        if (count($orders) === 0) {
            throw new OrderContainerFactoryException("Order not found");
        }

        return new OrderContainer($orders[0], $this->relationLoader);
    }

    public function loadByInvoiceUuidAndMerchantId(string $uuid, int $merchantId): OrderContainer
    {
        $order = $this->orderRepository->getByInvoiceAndMerchant($uuid, $merchantId);
        if ($order === null) {
            throw new OrderContainerFactoryException("Order not found");
        }

        return new OrderContainer($order, $this->relationLoader);
    }

    public function loadByMerchantIdAndExternalIdOrUuid(int $merchantId, string $orderId): OrderContainer
    {
        $order = $this->orderRepository->getOneByMerchantIdAndExternalCodeOrUUID($orderId, $merchantId);
        if (!$order) {
            throw new OrderContainerFactoryException("Order not found");
        }

        return new OrderContainer($order, $this->relationLoader);
    }

    public function loadByMerchantIdAndUuid(int $merchantId, string $uuid): OrderContainer
    {
        $order = $this->orderRepository->getOneByMerchantIdAndUUID($uuid, $merchantId);
        if (!$order) {
            throw new OrderContainerFactoryException("Order not found");
        }

        return new OrderContainer($order, $this->relationLoader);
    }

    public function loadNotYetConfirmedByCheckoutSessionUuid(string $checkoutSessionUuid): OrderContainer
    {
        $order = $this->orderRepository->getNotYetConfirmedByCheckoutSessionUuid($checkoutSessionUuid);
        if (!$order) {
            throw new OrderContainerFactoryException("Order not found");
        }

        return new OrderContainer($order, $this->relationLoader);
    }

    public function createFromNewOrderDTO(OrderCreationDTO $newOrder): OrderContainer
    {
        return (new OrderContainer($newOrder->getOrder(), $this->relationLoader))
            ->setDebtorPerson($newOrder->getDebtorPerson())
            ->setDebtorExternalData($newOrder->getDebtorExternalData())
            ->setDebtorExternalDataAddress($newOrder->getDebtorExternalDataAddress())
            ->setDeliveryAddress($newOrder->getDeliveryAddress())
            ->setOrderFinancialDetails($newOrder->getFinancialDetails())
            ->setLineItems($newOrder->getLineItems())
            ->setBillingAddress($newOrder->getBillingAddress())
            ->setRiskCheckResultCollection(new CheckResultCollection());
    }
}
