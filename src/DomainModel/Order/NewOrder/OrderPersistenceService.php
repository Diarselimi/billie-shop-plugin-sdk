<?php

namespace App\DomainModel\Order\NewOrder;

use App\Application\UseCase\CreateOrder\CreateOrderRequestInterface;
use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderLineItemRequest;
use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressEntityFactory;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntityFactory;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Order\OrderFactory;
use App\DomainModel\Order\OrderRepository;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsFactory;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use App\DomainModel\OrderLineItem\OrderLineItemEntity;
use App\DomainModel\OrderLineItem\OrderLineItemFactory;
use App\DomainModel\OrderLineItem\OrderLineItemRepositoryInterface;
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

    private $orderLineItemFactory;

    private $orderLineItemRepository;

    public function __construct(
        OrderRepository $orderRepository,
        OrderFinancialDetailsRepositoryInterface $orderFinancialDetailsRepository,
        PersonRepositoryInterface $personRepository,
        AddressRepositoryInterface $addressRepository,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        OrderFactory $orderFactory,
        PersonEntityFactory $personFactory,
        AddressEntityFactory $addressFactory,
        DebtorExternalDataEntityFactory $debtorExternalDataFactory,
        OrderFinancialDetailsFactory $orderFinancialDetailsFactory,
        ArrayHasherInterface $arrayHasher,
        OrderLineItemFactory $orderLineItemFactory,
        OrderLineItemRepositoryInterface $orderLineItemRepository
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
        $this->orderLineItemFactory = $orderLineItemFactory;
        $this->orderLineItemRepository = $orderLineItemRepository;
    }

    public function persistFromRequest(CreateOrderRequestInterface $request): OrderCreationDTO
    {
        $order = $this->orderFactory->createFromRequest($request);

        $debtorPerson = $this->persistDebtorPerson($request);
        $debtorAddress = $this->persistDebtorAddress($request);
        $billingAddress = $request->getBillingAddress() ? $this->persistAddress($request->getBillingAddress()) : $debtorAddress;
        $deliveryAddress = $request->getDeliveryAddress() ? $this->persistAddress($request->getDeliveryAddress()) : $billingAddress;

        $debtorExternalData = $this->persistDebtorExternalData(
            $request,
            $debtorAddress->getId(),
            $billingAddress->getId()
        );

        $order
            ->setDebtorPersonId($debtorPerson->getId())
            ->setDeliveryAddressId($deliveryAddress->getId())
            ->setDebtorExternalDataId($debtorExternalData->getId());
        $this->orderRepository->insert($order);

        $orderFinancialDetails = $this->persistOrderFinancialDetails($order->getId(), $request);

        $lineItems = $this->persistLineItems($order->getId(), $request->getLineItems());

        return new OrderCreationDTO(
            $order,
            $orderFinancialDetails,
            $debtorPerson,
            $debtorExternalData,
            $debtorAddress,
            $deliveryAddress,
            $billingAddress,
            $lineItems
        );
    }

    private function persistDebtorPerson(CreateOrderRequestInterface $request): PersonEntity
    {
        $debtorPerson = $this->personFactory->createFromRequest($request);
        $this->personRepository->insert($debtorPerson);

        return $debtorPerson;
    }

    private function persistDebtorAddress(CreateOrderRequestInterface $request): AddressEntity
    {
        $debtorAddress = $this->addressFactory->createFromRequestDebtor($request);
        $this->addressRepository->insert($debtorAddress);

        return $debtorAddress;
    }

    public function persistAddress(CreateOrderAddressRequest $addressRequest): AddressEntity
    {
        $address = $this->addressFactory->createFromAddressRequest($addressRequest);
        $this->addressRepository->insert($address);

        return $address;
    }

    private function persistDebtorExternalData(
        CreateOrderRequestInterface $request,
        int $addressId,
        ?int $invoiceAddressId
    ): DebtorExternalDataEntity {
        $debtorExternalData = $this->debtorExternalDataFactory
            ->createFromRequest($request)
            ->setAddressId($addressId)
            ->setBillingAddressId($invoiceAddressId);

        $debtorExternalData->setDataHash($this->arrayHasher->generateHash($request));

        $this->debtorExternalDataRepository->insert($debtorExternalData);

        return $debtorExternalData;
    }

    private function persistOrderFinancialDetails(
        int $orderId,
        CreateOrderRequestInterface $request
    ): OrderFinancialDetailsEntity {
        $orderFinancialDetails = $this->orderFinancialDetailsFactory->create(
            $orderId,
            $request->getAmount(),
            $request->getDuration(),
            $request->getAmount()
        );

        $this->orderFinancialDetailsRepository->insert($orderFinancialDetails);

        return $orderFinancialDetails;
    }

    /**
     * @return OrderLineItemEntity[]
     */
    private function persistLineItems(int $orderId, array $lineItems): array
    {
        return array_map(
            function (CreateOrderLineItemRequest $request) use ($orderId) {
                $orderLineItem = $this->orderLineItemFactory->createFromRequest($orderId, $request);
                $this->orderLineItemRepository->insert($orderLineItem);

                return $orderLineItem;
            },
            $lineItems
        );
    }
}
