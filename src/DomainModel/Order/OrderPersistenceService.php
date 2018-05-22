<?php

namespace App\DomainModel\Order;

use App\Application\PaellaCoreCriticalException;
use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\DomainModel\Address\AddressEntityFactory;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntityFactory;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Exception\RepositoryException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
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

        try {
            // debtor person
            $debtorPerson = $this->personFactory->createFromRequest($request);
            $this->personRepository->insert($debtorPerson);
            $order->setDebtorPersonId($debtorPerson->getId());

            // delivery address
            $deliveryAddress = $this->addressFactory->createFromRequestDelivery($request);
            $this->addressRepository->insert($deliveryAddress);
            $order->setDeliveryAddressId($deliveryAddress->getId());

            // debtor address
            $debtorAddress = $this->addressFactory->createFromRequestDebtor($request);
            $this->addressRepository->insert($debtorAddress);

            // debtor external data
            $debtorExternalData = $this->debtorExternalDataFactory
                ->createFromRequest($request)
                ->setAddressId($debtorAddress->getId())
            ;
            $this->debtorExternalDataRepository->insert($debtorExternalData);
            $order->setDebtorExternalDataId($debtorExternalData->getId());

            // order
            $this->orderRepository->insert($order);

            return (new OrderContainer())
                ->setOrder($order)
                ->setDebtorPerson($debtorPerson)
                ->setDebtorExternalData($debtorExternalData)
                ->setDebtorExternalDataAddress($debtorAddress)
                ->setDeliveryAddress($deliveryAddress)
                ->setMerchant($this->merchantRepository->getOneById($order->getMerchantId()))
            ;
        } catch (RepositoryException $exception) {
            throw new PaellaCoreCriticalException(
                "Order can't be persisted",
                PaellaCoreCriticalException::CODE_ORDER_COULD_NOT_BE_PERSISTED
            );
        }
    }
}
