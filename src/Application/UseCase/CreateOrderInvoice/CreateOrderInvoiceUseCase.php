<?php

namespace App\Application\UseCase\CreateOrderInvoice;

use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceFactory;
use App\DomainModel\OrderInvoice\OrderInvoiceRepositoryInterface;

class CreateOrderInvoiceUseCase
{
    private $orderRepository;

    private $orderInvoiceRepository;

    private $orderInvoiceFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderInvoiceRepositoryInterface $orderInvoiceRepository,
        OrderInvoiceFactory $orderInvoiceFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderInvoiceRepository = $orderInvoiceRepository;
        $this->orderInvoiceFactory = $orderInvoiceFactory;
    }

    public function execute(CreateOrderInvoiceRequest $request): void
    {
        $order = $this->orderRepository->getOneByMerchantIdAndExternalCodeOrUUID($request->getOrderId(), $request->getMerchantId());

        if (!$order) {
            throw new OrderNotFoundException();
        }

        $orderInvoice = $this->orderInvoiceFactory->create($order->getId(), $request->getFileId(), $request->getInvoiceNumber());
        $this->orderInvoiceRepository->insert($orderInvoice);
    }
}
