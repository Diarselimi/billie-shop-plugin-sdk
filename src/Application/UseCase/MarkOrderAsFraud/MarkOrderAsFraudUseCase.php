<?php

namespace App\Application\UseCase\MarkOrderAsFraud;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;

class MarkOrderAsFraudUseCase
{
    const ORDER_AMOUNT_LIMIT = 2000;

    private $orderRepository;

    private $orderStateManager;

    private $addressRepository;

    private $debtorExternalDataRepository;

    private $borscht;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        AddressRepositoryInterface $addressRepository,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        BorschtInterface $borscht
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderStateManager = $orderStateManager;
        $this->addressRepository = $addressRepository;
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
        $this->borscht = $borscht;
    }

    public function execute(MarkOrderAsFraudRequest $request): void
    {
        $uuid = $request->getUuid();
        $order = $this->orderRepository->getOneByUuid($request->getUuid());

        if (!$order) {
            throw new OrderNotFoundException("Order with UUID: $uuid not found");
        }

        if ($order->getMarkedAsFraudAt()) {
            throw new FraudOrderException();
        }

        $order->setMarkedAsFraudAt(new \DateTime());
        $this->orderRepository->update($order);

        if (!$this->isEligibleForFraudReclaim($order)) {
            throw new FraudReclaimActionException();
        }

        $this->borscht->createFraudReclaim($order->getPaymentId());
    }

    private function isDeliveryAddressDifferentToDebtorAddress(AddressEntity $deliveryAddress, AddressEntity $debtorAddress): bool
    {
        return $deliveryAddress->getCity() !== $debtorAddress->getCity() ||
            $deliveryAddress->getPostalCode() !== $debtorAddress->getPostalCode() ||
            $deliveryAddress->getStreet() !== $debtorAddress->getStreet() ||
            $deliveryAddress->getHouseNumber() !== $debtorAddress->getHouseNumber();
    }

    private function isEligibleForFraudReclaim(OrderEntity $order): bool
    {
        $deliveryAddress = $this->addressRepository->getOneById($order->getDeliveryAddressId());

        $debtorData = $this->debtorExternalDataRepository->getOneById($order->getDebtorExternalDataId());
        $debtorAddress = $this->addressRepository->getOneById($debtorData->getAddressId());

        return ($this->orderStateManager->isLate($order) || $this->orderStateManager->isPaidOut($order)) &&
            $this->isDeliveryAddressDifferentToDebtorAddress($deliveryAddress, $debtorAddress) &&
            ($debtorData->isEstablishedCustomer() === null || $order->getAmountGross() > self::ORDER_AMOUNT_LIMIT)
        ;
    }
}
