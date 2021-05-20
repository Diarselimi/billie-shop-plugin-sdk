<?php

declare(strict_types=1);

namespace App\Application\UseCase\UpdateOrderWithInvoice;

use App\Application\Exception\OrderBeingCollectedException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\LegacyUpdateOrder\LegacyUpdateOrderRequest;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\SalesforceInterface;
use App\DomainModel\OrderInvoiceDocument\InvoiceDocumentCreator;
use App\DomainModel\OrderUpdate\LegacyUpdateOrderService;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class UpdateOrderWithInvoiceUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private OrderContainerFactory $orderContainerFactory;

    private InvoiceDocumentCreator $invoiceDocumentCreator;

    private SalesforceInterface $salesforce;

    private LegacyUpdateOrderService $legacyUpdateOrderService;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        InvoiceDocumentCreator $orderinvoiceDocumentCreator,
        SalesforceInterface $salesforce,
        LegacyUpdateOrderService $legacyUpdateOrderService
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->invoiceDocumentCreator = $orderinvoiceDocumentCreator;
        $this->salesforce = $salesforce;
        $this->legacyUpdateOrderService = $legacyUpdateOrderService;
    }

    public function execute(UpdateOrderWithInvoiceRequest $request): void
    {
        $this->validateRequest($request);

        try {
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndUuid(
                $request->getMerchantId(),
                $request->getOrderId()
            );

            $order = $orderContainer->getOrder();
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        if ($orderContainer->getOrder()->wasShipped()) {
            $this->validateRequest($request, null, ['InvoiceNumber']);

            if ($request->getInvoiceNumber() !== $order->getInvoiceNumber()) {
                $this->validateRequest($request, null, ['InvoiceFile']);
            }
        }

        if ($this->isOrderLateAndInCollections($order)) {
            throw new OrderBeingCollectedException();
        }

        $legacyUpdateOrderRequest = (new LegacyUpdateOrderRequest($request->getOrderId(), $request->getMerchantId()))
            ->setAmount($request->getAmount())
            ->setInvoiceNumber($request->getInvoiceNumber())
            ->setInvoiceUrl($order->getInvoiceUrl());
        $this->validator->validate($legacyUpdateOrderRequest);
        $changeSet = $this->legacyUpdateOrderService->update($orderContainer, $legacyUpdateOrderRequest);

        if ($request->getInvoiceFile() !== null && $orderContainer->getOrder()->wasShipped()) {
            $invoice = $orderContainer->getInvoices()->getLastInvoice();

            $this->invoiceDocumentCreator->createFromUpload(
                $order->getId(),
                $invoice ? $invoice->getUuid() : null,
                $order->getInvoiceNumber(),
                $request->getInvoiceFile()
            );
        }

        $this->logInfo('Order updated, state {state}.', [
            LoggingInterface::KEY_NAME => $order->getState(),
            LoggingInterface::KEY_NUMBER => (int) $changeSet->getAmount() !== null,
        ]);
    }

    private function isOrderLateAndInCollections(OrderEntity $order): bool
    {
        if (!$order->isLate()) {
            return false;
        }

        return $this->salesforce->getOrderCollectionsStatus($order->getUuid()) !== null;
    }
}
