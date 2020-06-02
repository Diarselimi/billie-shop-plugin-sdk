<?php

declare(strict_types=1);

namespace App\Application\UseCase\ShipOrderWithInvoice;

use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\ShipOrder\ShipOrderService;
use App\Helper\Uuid\UuidGeneratorInterface;

class ShipOrderWithInvoiceUseCase
{
    protected $invoiceManager;

    private $orderContainerFactory;

    private $uuidGenerator;

    private $shipOrderService;

    public function __construct(
        OrderInvoiceManager $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        UuidGeneratorInterface $uuidGenerator,
        ShipOrderService $shipOrderService
    ) {
        $this->invoiceManager = $invoiceManager;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->uuidGenerator = $uuidGenerator;
        $this->shipOrderService = $shipOrderService;
    }

    public function execute(ShipOrderWithInvoiceRequest $request): OrderResponse
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndUuid(
                $request->getMerchantId(),
                $request->getOrderId()
            );
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        $order = $orderContainer->getOrder();

        $this->shipOrderService->validate($request, $order);
        $this->addRequestData($request, $order);

        $shippedInPayments = $this->shipOrderService->hasPaymentDetails($orderContainer);

        $this->invoiceManager->uploadInvoiceFile($order, $request->getInvoiceFile());

        return $this->shipOrderService->shipOrder($orderContainer, $shippedInPayments);
    }

    private function addRequestData(ShipOrderWithInvoiceRequest $request, OrderEntity $order): void
    {
        $order->setInvoiceNumber($request->getInvoiceNumber());

        if ($request->getExternalCode() && !$order->getExternalCode()) {
            $order->setExternalCode($request->getExternalCode());
        }

        if (!$order->getPaymentId()) {
            $order->setPaymentId($this->uuidGenerator->uuid4());
            $order->setShippedAt(new \DateTime());
        }
    }
}
