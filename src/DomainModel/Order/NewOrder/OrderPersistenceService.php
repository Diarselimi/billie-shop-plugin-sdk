<?php

namespace App\DomainModel\Order\NewOrder;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressEntityFactory;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntityFactory;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Order\OrderEntityFactory;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsFactory;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use App\DomainModel\Person\PersonEntity;
use App\DomainModel\Person\PersonEntityFactory;
use App\DomainModel\Person\PersonRepositoryInterface;
use App\Helper\Hasher\ArrayHasherInterface;

class OrderPersistenceService
{
    private $orderRepository;

    private $orderFinancialDetailsRepository;

    private $personRepository;

    private $addressRepository;

    private $debtorExternalDataRepository;

    private $orderFactory;

    private $personFactory;

    private $addressFactory;

    private $debtorExternalDataFactory;

    private $orderFinancialDetailsFactory;

    private $arrayHasher;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderFinancialDetailsRepositoryInterface  $orderFinancialDetailsRepository,
        PersonRepositoryInterface $personRepository,
        AddressRepositoryInterface $addressRepository,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        OrderEntityFactory $orderFactory,
        PersonEntityFactory $personFactory,
        AddressEntityFactory $addressFactory,
        DebtorExternalDataEntityFactory $debtorExternalDataFactory,
        OrderFinancialDetailsFactory $orderFinancialDetailsFactory,
        ArrayHasherInterface $arrayHasher
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderFinancialDetailsRepository = $orderFinancialDetailsRepository;
        $this->personRepository = $personRepository;
        $this->addressRepository = $addressRepository;
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
        $this->orderFactory = $orderFactory;
        $this->personFactory = $personFactory;
        $this->addressFactory = $addressFactory;
        $this->debtorExternalDataFactory = $debtorExternalDataFactory;
        $this->orderFinancialDetailsFactory = $orderFinancialDetailsFactory;
        $this->arrayHasher = $arrayHasher;
    }

    public function persistFromRequest(CreateOrderRequest $request): OrderCreationDTO
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

        $orderFinancialDetails = $this->persistOrderFinancialDetails($order->getId(), $request);

        return new OrderCreationDTO($order, $orderFinancialDetails, $debtorPerson, $debtorExternalData, $debtorAddress, $deliveryAddress);
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

        $debtorExternalData->setDataHash($this->arrayHasher->generateHash($request));

        $this->debtorExternalDataRepository->insert($debtorExternalData);

        return $debtorExternalData;
    }

    private function persistOrderFinancialDetails(int $orderId, CreateOrderRequest $request): OrderFinancialDetailsEntity
    {
        $orderFinancialDetails = $this->orderFinancialDetailsFactory->create(
            $orderId,
            $request->getAmount()->getGross(),
            $request->getAmount()->getNet(),
            $request->getAmount()->getTax(),
            $request->getDuration()
        );

        $this->orderFinancialDetailsRepository->insert($orderFinancialDetails);

        return $orderFinancialDetails;
    }
}
