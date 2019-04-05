<?php

namespace App\Application\UseCase\CreateOrderInvoice;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceEntity;
use App\DomainModel\OrderInvoice\OrderInvoiceRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

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
            throw new PaellaCoreCriticalException(
                "Order #{$request->getOrderId()} not found",
                PaellaCoreCriticalException::CODE_NOT_FOUND,
                Response::HTTP_NOT_FOUND
            );
        }

        $orderInvoice = (new OrderInvoiceEntity())
            ->setOrderId($order->getId())
            ->setFileId($request->getFileId())
            ->setInvoiceNumber($request->getInvoiceNumber())
        ;
        $this->orderInvoiceRepository->insert($orderInvoice);
    }
}
