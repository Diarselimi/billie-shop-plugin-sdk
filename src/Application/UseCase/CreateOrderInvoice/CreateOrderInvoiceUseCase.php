<?php

namespace App\Application\UseCase\CreateOrderInvoice;

use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceEntity;
use App\DomainModel\OrderInvoice\OrderInvoiceRepositoryInterface;

class CreateOrderInvoiceUseCase
{
    private $orderRepository;

    private $orderInvoiceRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderInvoiceRepositoryInterface $orderInvoiceRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderInvoiceRepository = $orderInvoiceRepository;
    }

    public function execute(CreateOrderInvoiceRequest $request): void
    {
        $order = $this->orderRepository->getOneByMerchantIdAndExternalCodeOrUUID($request->getOrderId(), $request->getMerchantId());

        if (!$order) {
            throw new OrderNotFoundException("Order #{$request->getOrderId()} does't exist");
        }

        $orderInvoice = (new OrderInvoiceEntity())
            ->setOrderId($order->getId())
            ->setFileId($request->getFileId())
            ->setInvoiceNumber($request->getInvoiceNumber())
        ;

        $this->orderInvoiceRepository->insert($orderInvoice);
    }
}
