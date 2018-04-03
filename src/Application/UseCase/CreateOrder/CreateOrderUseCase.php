<?php

namespace App\Application\UseCase\CreateOrder;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Address\AddressEntityFactory;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\Exception\RepositoryException;
use App\DomainModel\Order\OrderEntityFactory;
use App\DomainModel\Order\OrderRepositoryInterface;

class CreateOrderUseCase
{
    private $orderRepository;
    private $addressRepository;
    private $orderFactory;
    private $addressFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        AddressRepositoryInterface $addressRepository,
        OrderEntityFactory $orderFactory,
        AddressEntityFactory $addressFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->addressRepository = $addressRepository;
        $this->orderFactory = $orderFactory;
        $this->addressFactory = $addressFactory;
    }

    public function execute(CreateOrderRequest $request)
    {
        $order = $this->orderFactory->createFromRequest($request);

        $deliveryAddress = $this->addressFactory->createFromRequestDelivery($request);

        try {
            $this->addressRepository->insert($deliveryAddress);
        } catch (RepositoryException $exception) {
            throw new PaellaCoreCriticalException();
        }

        $order->setDeliveryAddressId($deliveryAddress->getId());

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
