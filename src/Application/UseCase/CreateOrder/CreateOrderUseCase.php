<?php

namespace App\Application\UseCase\CreateOrder;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Address\AddressEntityFactory;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntityFactory;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Exception\RepositoryException;
use App\DomainModel\Order\OrderEntityFactory;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Person\PersonEntityFactory;
use App\DomainModel\Person\PersonRepositoryInterface;

class CreateOrderUseCase
{
    private $orderRepository;
    private $personRepository;
    private $addressRepository;
    private $debtorExternalDataRepository;
    private $orderFactory;
    private $personFactory;
    private $addressFactory;
    private $debtorExternalDataFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        PersonRepositoryInterface $personRepository,
        AddressRepositoryInterface $addressRepository,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        OrderEntityFactory $orderFactory,
        PersonEntityFactory $personFactory,
        AddressEntityFactory $addressFactory,
        DebtorExternalDataEntityFactory $debtorExternalDataFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->personRepository = $personRepository;
        $this->addressRepository = $addressRepository;
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
        $this->orderFactory = $orderFactory;
        $this->personFactory = $personFactory;
        $this->addressFactory = $addressFactory;
        $this->debtorExternalDataFactory = $debtorExternalDataFactory;
    }

    public function execute(CreateOrderRequest $request)
    {
        $order = $this->orderFactory->createFromRequest($request);

        // debtor person
        $debtorPerson = $this->personFactory->createFromRequest($request);
        try {
            $this->personRepository->insert($debtorPerson);
        } catch (RepositoryException $exception) {
            throw new PaellaCoreCriticalException();
        }
        $order->setDebtorPersonId($debtorPerson->getId());

        // delivery address
        $deliveryAddress = $this->addressFactory->createFromRequestDelivery($request);
        try {
            $this->addressRepository->insert($deliveryAddress);
        } catch (RepositoryException $exception) {
            throw new PaellaCoreCriticalException();
        }
        $order->setDeliveryAddressId($deliveryAddress->getId());

        // debtor address
        $debtorAddress = $this->addressFactory->createFromRequestDebtor($request);
        try {
            $this->addressRepository->insert($debtorAddress);
        } catch (RepositoryException $exception) {
            throw new PaellaCoreCriticalException();
        }

        // debtor external data
        $debtorExternalData = $this->debtorExternalDataFactory
            ->createFromRequest($request)
            ->setAddressId($debtorAddress->getId())
        ;
        try {
            $this->debtorExternalDataRepository->insert($debtorExternalData);
        } catch (RepositoryException $exception) {
            throw new PaellaCoreCriticalException();
        }
        $order->setDebtorExternalDataId($debtorExternalData->getId());

        // order
        try {
            $this->orderRepository->insert($order);
        } catch (RepositoryException $exception) {
            throw new PaellaCoreCriticalException();
        }
    }
}
