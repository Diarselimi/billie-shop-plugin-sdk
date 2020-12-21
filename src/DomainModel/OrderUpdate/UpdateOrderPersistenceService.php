<?php

namespace App\DomainModel\OrderUpdate;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsPersistenceService;
use App\DomainModel\OrderInvoice\OrderInvoiceEntity;
use App\DomainModel\OrderInvoiceDocument\InvoiceDocumentUploadException;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\InvoiceDocumentUploadHandlerInterface;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\InvoiceDocumentUploadHandlerAggregator;
use App\DomainModel\Payment\PaymentRequestFactory;
use App\DomainModel\Payment\PaymentsServiceInterface;

class UpdateOrderPersistenceService
{
    private PaymentsServiceInterface $paymentsService;

    private OrderRepositoryInterface $orderRepository;

    private InvoiceDocumentUploadHandlerAggregator $invoiceUrlHandler;

    private OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService;

    private UpdateOrderLimitsService $updateOrderLimitsService;

    private PaymentRequestFactory $paymentRequestFactory;

    private UpdateOrderRequestValidator $updateOrderRequestValidator;

    public function __construct(
        PaymentsServiceInterface $paymentsService,
        OrderRepositoryInterface $orderRepository,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        InvoiceDocumentUploadHandlerAggregator $invoiceUrlHandler,
        PaymentRequestFactory $paymentRequestFactory,
        UpdateOrderLimitsService $updateOrderLimitsService,
        UpdateOrderRequestValidator $updateOrderRequestValidator
    ) {
        $this->paymentsService = $paymentsService;
        $this->orderRepository = $orderRepository;
        $this->financialDetailsPersistenceService = $financialDetailsPersistenceService;
        $this->invoiceUrlHandler = $invoiceUrlHandler;
        $this->paymentRequestFactory = $paymentRequestFactory;
        $this->updateOrderLimitsService = $updateOrderLimitsService;
        $this->updateOrderRequestValidator = $updateOrderRequestValidator;
    }

    public function update(OrderContainer $orderContainer, UpdateOrderRequest $request): UpdateOrderRequest
    {
        $order = $orderContainer->getOrder();
        if ($order->isWorkflowV2()) {
            throw new WorkflowException('Order workflow v2 is not supported by API v1');
        }
        $changeSet = $this->updateOrderRequestValidator->getValidatedRequest($orderContainer, $request);

        $amountChanged = $changeSet->getAmount() !== null;
        $durationChanged = $changeSet->getDuration() !== null;
        $invoiceChanged = $changeSet->getInvoiceUrl() !== null || $changeSet->getInvoiceNumber() !== null;
        $externalCodeChanged = $changeSet->getExternalCode() !== null;

        // Persist only what was changed:

        if ($amountChanged && !$order->wasShipped()) {
            $this->updateOrderLimitsService->unlockLimits($orderContainer, $changeSet);
        }

        if ($amountChanged || $durationChanged) {
            $duration = $changeSet->getDuration() !== null
                ? $changeSet->getDuration()
                : $orderContainer->getOrderFinancialDetails()->getDuration();
            $this->financialDetailsPersistenceService->updateFinancialDetails(
                $orderContainer,
                $changeSet,
                $duration
            );
        }

        if ($invoiceChanged || $externalCodeChanged) {
            $this->updateOrder($orderContainer, $changeSet);
        }

        if ($invoiceChanged) {
            $this->updateInvoice($order);
        }

        if (($amountChanged || $invoiceChanged || $durationChanged) && $order->wasShipped()) {
            $this->paymentsService->modifyOrder(
                $this->paymentRequestFactory->createModifyRequestDTO($orderContainer)
            );
        }

        return $changeSet;
    }

    private function updateOrder(OrderContainer $orderContainer, UpdateOrderRequest $changeSet): void
    {
        $order = $orderContainer->getOrder();

        if ($changeSet->getExternalCode()) {
            $order->setExternalCode($changeSet->getExternalCode());
        }
        if ($changeSet->getInvoiceNumber()) {
            $order->setInvoiceNumber($changeSet->getInvoiceNumber());
        }
        if ($changeSet->getInvoiceUrl()) {
            $order->setInvoiceUrl($changeSet->getInvoiceUrl());
        }
        $this->orderRepository->update($order);
    }

    private function updateInvoice(OrderEntity $order): void
    {
        try {
            $this->invoiceUrlHandler->handle(
                $order,
                $order->getUuid(),
                $order->getInvoiceUrl(),
                $order->getInvoiceNumber(),
                InvoiceDocumentUploadHandlerInterface::EVENT_SOURCE_UPDATE
            );
        } catch (InvoiceDocumentUploadException $exception) {
            throw new UpdateOrderException("Order invoice cannot be updated: upload failed.", 0, $exception);
        }
    }
}
