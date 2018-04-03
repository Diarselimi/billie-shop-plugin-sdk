<?php

namespace App\Application\UseCase\CreateOrder;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Address\AddressEntityFactory;
use App\DomainModel\Address\AddressRepositoryInterface;
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
    private $orderFactory;
    private $personFactory;
    private $addressFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        PersonRepositoryInterface $personRepository,
        AddressRepositoryInterface $addressRepository,
        OrderEntityFactory $orderFactory,
        PersonEntityFactory $personFactory,
        AddressEntityFactory $addressFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->personRepository = $personRepository;
        $this->addressRepository = $addressRepository;
        $this->orderFactory = $orderFactory;
        $this->personFactory = $personFactory;
        $this->addressFactory = $addressFactory;
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

        try {
            $this->orderRepository->insert($order);
        } catch (RepositoryException $exception) {
            throw new PaellaCoreCriticalException();
        }
    }
}
