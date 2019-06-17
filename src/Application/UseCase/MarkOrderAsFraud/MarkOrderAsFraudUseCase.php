<?php

namespace App\Application\UseCase\MarkOrderAsFraud;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;

class MarkOrderAsFraudUseCase
{
    const ORDER_AMOUNT_LIMIT = 2000;

    private $orderRepository;

    private $orderStateManager;

    private $orderContainerFactory;

    private $paymentsService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        OrderContainerFactory $orderContainerFactory,
        BorschtInterface $paymentsService
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderStateManager = $orderStateManager;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->paymentsService = $paymentsService;
    }

    public function execute(MarkOrderAsFraudRequest $request): void
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadByUuid($request->getUuid());
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        $order = $orderContainer->getOrder();

        if ($order->getMarkedAsFraudAt()) {
            throw new FraudOrderException();
        }

        $order->setMarkedAsFraudAt(new \DateTime());
        $this->orderRepository->update($order);

        if (!$this->isEligibleForFraudReclaim($orderContainer)) {
            throw new FraudReclaimActionException();
        }

        $this->paymentsService->createFraudReclaim($order->getPaymentId());
    }

    private function isDeliveryAddressDifferentToDebtorAddress(AddressEntity $deliveryAddress, AddressEntity $debtorAddress): bool
    {
        return $deliveryAddress->getCity() !== $debtorAddress->getCity() ||
            $deliveryAddress->getPostalCode() !== $debtorAddress->getPostalCode() ||
            $deliveryAddress->getStreet() !== $debtorAddress->getStreet() ||
            $deliveryAddress->getHouseNumber() !== $debtorAddress->getHouseNumber();
    }

    private function isEligibleForFraudReclaim(OrderContainer $orderContainer): bool
    {
        $order = $orderContainer->getOrder();

        return ($this->orderStateManager->isLate($order) || $this->orderStateManager->isPaidOut($order)) &&
            $this->isDeliveryAddressDifferentToDebtorAddress($orderContainer->getDeliveryAddress(), $orderContainer->getDebtorExternalDataAddress()) &&
            ($orderContainer->getDebtorExternalData()->isEstablishedCustomer() === null || $orderContainer->getOrderFinancialDetails()->getAmountGross() > self::ORDER_AMOUNT_LIMIT)
        ;
    }
}
