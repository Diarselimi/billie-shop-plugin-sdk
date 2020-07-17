<?php

declare(strict_types=1);

namespace App\Application\UseCase\ShipOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderInvoice\InvoiceUploadHandlerInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use App\DomainModel\OrderInvoice\OrderInvoiceUploadException;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\ShipOrder\ShipOrderException;
use App\DomainModel\ShipOrder\ShipOrderService;
use App\Helper\Uuid\UuidGeneratorInterface;

class ShipOrderUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

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

    public function execute(ShipOrderRequest $request): OrderResponse
    {
        try {
            $orderContainer = $this->orderContainerFactory
                ->loadByMerchantIdAndExternalIdOrUuid($request->getMerchantId(), $request->getOrderId());
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        $order = $orderContainer->getOrder();

        $this->shipOrderService->validate($request, $order);
        $this->addRequestData($request, $order);

        $shippedInPayments = $this->shipOrderService->hasPaymentDetails($orderContainer);

        $this->uploadInvoice($order);

        return $this->shipOrderService->shipOrder($orderContainer, $shippedInPayments);
    }

    private function addRequestData(ShipOrderRequest $request, OrderEntity $order): void
    {
        $order
            ->setInvoiceNumber($request->getInvoiceNumber())
            ->setInvoiceUrl($request->getInvoiceUrl())
            ->setProofOfDeliveryUrl($request->getShippingDocumentUrl());

        if ($request->getExternalCode() && !$order->getExternalCode()) {
            $order->setExternalCode($request->getExternalCode());
        }

        if (!$order->getPaymentId()) {
            $order->setPaymentId($this->uuidGenerator->uuid4());
            $order->setShippedAt(new \DateTime());
        }
    }

    private function uploadInvoice(OrderEntity $order): void
    {
        try {
            $this->invoiceManager->upload($order, InvoiceUploadHandlerInterface::EVENT_SHIPMENT);
        } catch (OrderInvoiceUploadException $exception) {
            throw new ShipOrderException("Invoice can't be scheduled for upload", 0, $exception);
        }
    }
}
