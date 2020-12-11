<?php

namespace App\Application\UseCase\CreateOrderInvoice;

use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderInvoice\LegacyOrderInvoiceFactory;
use App\DomainModel\OrderInvoice\LegacyOrderInvoiceRepositoryInterface;

class CreateOrderInvoiceUseCase
{
    private $orderRepository;

    private $orderInvoiceRepository;

    private $orderInvoiceFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        LegacyOrderInvoiceRepositoryInterface $orderInvoiceRepository,
        LegacyOrderInvoiceFactory $orderInvoiceFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderInvoiceRepository = $orderInvoiceRepository;
        $this->orderInvoiceFactory = $orderInvoiceFactory;
    }

    public function execute(CreateOrderInvoiceRequest $request): void
    {
        $order = $this->orderRepository->getOneByUuid($request->getOrderUuid());

        if (!$order) {
            throw new OrderNotFoundException();
        }

        $orderInvoice = $this->orderInvoiceFactory->create($order->getId(), $request->getFileId(), $request->getInvoiceNumber());
        $this->orderInvoiceRepository->insert($orderInvoice);
    }
}
