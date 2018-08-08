<?php

namespace App\DomainModel\Order;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressEntityFactory;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntityFactory;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\Person\PersonEntity;
use App\DomainModel\Person\PersonEntityFactory;
use App\DomainModel\Person\PersonRepositoryInterface;

class OrderPersistenceService
{
    private $orderRepository;
    private $personRepository;
    private $addressRepository;
    private $debtorExternalDataRepository;
    private $merchantRepository;
    private $orderFactory;
    private $personFactory;
    private $addressFactory;
    private $debtorExternalDataFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        PersonRepositoryInterface $personRepository,
        AddressRepositoryInterface $addressRepository,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        MerchantRepositoryInterface $merchantRepository,
        OrderEntityFactory $orderFactory,
        PersonEntityFactory $personFactory,
        AddressEntityFactory $addressFactory,
        DebtorExternalDataEntityFactory $debtorExternalDataFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->personRepository = $personRepository;
        $this->addressRepository = $addressRepository;
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
        $this->merchantRepository = $merchantRepository;
        $this->orderFactory = $orderFactory;
        $this->personFactory = $personFactory;
        $this->addressFactory = $addressFactory;
        $this->debtorExternalDataFactory = $debtorExternalDataFactory;
    }

    public function persistFromRequest(CreateOrderRequest $request): OrderContainer
    {
        $order = $this->orderFactory->createFromRequest($request);

        $debtorPerson = $this->persistDebtorPerson($request);
        $deliveryAddress = $this->persistDeliveryAddress($request);
        $debtorAddress = $this->persistDebtorAddress($request);
        $debtorExternalData = $this->persistDebtorExternalData($request, $debtorAddress->getId());

        $order
            ->setDebtorPersonId($debtorPerson->getId())
            ->setDeliveryAddressId($deliveryAddress->getId())
            ->setDebtorExternalDataId($debtorExternalData->getId())
        ;
        $this->orderRepository->insert($order);

        return (new OrderContainer())
            ->setOrder($order)
            ->setDebtorPerson($debtorPerson)
            ->setDebtorExternalData($debtorExternalData)
            ->setDebtorExternalDataAddress($debtorAddress)
            ->setDeliveryAddress($deliveryAddress)
            ->setMerchant($this->merchantRepository->getOneById($order->getMerchantId()))
        ;
    }

    private function persistDebtorPerson(CreateOrderRequest $request): PersonEntity
    {
        $debtorPerson = $this->personFactory->createFromRequest($request);
        $this->personRepository->insert($debtorPerson);

        return $debtorPerson;
    }

    private function persistDeliveryAddress(CreateOrderRequest $request): AddressEntity
    {
        $deliveryAddress = $this->addressFactory->createFromRequestDelivery($request);
        $this->addressRepository->insert($deliveryAddress);

        return $deliveryAddress;
    }

    private function persistDebtorAddress(CreateOrderRequest $request): AddressEntity
    {
        $debtorAddress = $this->addressFactory->createFromRequestDebtor($request);
        $this->addressRepository->insert($debtorAddress);

        return $debtorAddress;
    }

    private function persistDebtorExternalData(CreateOrderRequest $request, int $addressId): DebtorExternalDataEntity
    {
        $debtorExternalData = $this->debtorExternalDataFactory
            ->createFromRequest($request)
            ->setAddressId($addressId)
        ;
        $this->debtorExternalDataRepository->insert($debtorExternalData);

        return $debtorExternalData;
    }
}
