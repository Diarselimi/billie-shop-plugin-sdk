<?php

namespace App\DomainModel\OrderUpdate;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\LegacyUpdateOrder\LegacyUpdateOrderRequest;
use App\DomainModel\Invoice\ExtendInvoiceService;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsPersistenceService;
use App\DomainModel\OrderInvoiceDocument\InvoiceDocumentUploadException;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\InvoiceDocumentUploadHandlerAggregator;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\InvoiceDocumentUploadHandlerInterface;
use App\DomainModel\Payment\PaymentRequestFactory;
use App\DomainModel\Payment\PaymentsServiceInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class LegacyUpdateOrderService implements LoggingInterface
{
    use LoggingTrait;

    private PaymentsServiceInterface $paymentsService;

    private OrderRepositoryInterface $orderRepository;

    private InvoiceDocumentUploadHandlerAggregator $invoiceUrlHandler;

    private OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService;

    private UpdateOrderLimitsService $updateOrderLimitsService;

    private PaymentRequestFactory $paymentRequestFactory;

    private UpdateOrderRequestValidator $updateOrderRequestValidator;

    private ExtendInvoiceService $extendInvoiceService;

    public function __construct(
        PaymentsServiceInterface $paymentsService,
        OrderRepositoryInterface $orderRepository,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        InvoiceDocumentUploadHandlerAggregator $invoiceUrlHandler,
        PaymentRequestFactory $paymentRequestFactory,
        UpdateOrderLimitsService $updateOrderLimitsService,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        ExtendInvoiceService $extendInvoiceService
    ) {
        $this->paymentsService = $paymentsService;
        $this->orderRepository = $orderRepository;
        $this->financialDetailsPersistenceService = $financialDetailsPersistenceService;
        $this->invoiceUrlHandler = $invoiceUrlHandler;
        $this->paymentRequestFactory = $paymentRequestFactory;
        $this->updateOrderLimitsService = $updateOrderLimitsService;
        $this->updateOrderRequestValidator = $updateOrderRequestValidator;
        $this->extendInvoiceService = $extendInvoiceService;
    }

    public function update(OrderContainer $orderContainer, LegacyUpdateOrderRequest $request): void
    {
        $order = $orderContainer->getOrder();
        if ($order->isWorkflowV2()) {
            throw new WorkflowException('Order workflow v2 is not supported by API v1');
        }
        $changeSet = $this->updateOrderRequestValidator->getValidatedRequest($orderContainer, $request);

        $this->logChangeSet($orderContainer, $changeSet);

        if ($changeSet->isAmountChanged() && !$order->wasShipped()) {
            $this->updateOrderLimitsService->unlockLimits($orderContainer, $changeSet);
        }

        $this->doUpdate($orderContainer, $changeSet);
    }

    private function logChangeSet(OrderContainer $orderContainer, LegacyUpdateOrderRequest $changeSet): void
    {
        $this->logInfo(
            'Start order update, state {name}.',
            [
                LoggingInterface::KEY_NAME => $orderContainer->getOrder()->getState(),
                LoggingInterface::KEY_SOBAKA => [
                    'duration_changed' => (int) $changeSet->isDurationChanged(),
                    'invoice_changed' => (int) (
                        $changeSet->isInvoiceNumberChanged() || $changeSet->isInvoiceUrlChanged()
                    ),
                    'amount_changed' => (int) $changeSet->isAmountChanged(),
                    'external_code_changed' => (int) $changeSet->isExternalCodeChanged(),
                ],
            ]
        );
    }

    private function retrieveSingleInvoice(OrderContainer $orderContainer): ?Invoice
    {
        $invoices = $orderContainer->getInvoices();
        if (count($invoices) === 1) {
            return array_pop($invoices);
        }

        return null;
    }

    private function doUpdate(OrderContainer $orderContainer, LegacyUpdateOrderRequest $changeSet): void
    {
        if (
            $changeSet->isAmountChanged()
            || $changeSet->isDurationChanged()
        ) {
            $duration = $changeSet->isDurationChanged()
                ? $changeSet->getDuration()
                : $orderContainer->getOrderFinancialDetails()->getDuration();

            $this->financialDetailsPersistenceService->updateFinancialDetails(
                $orderContainer,
                $changeSet,
                $duration
            );
        }

        if (
            $changeSet->isInvoiceNumberChanged()
            || $changeSet->isInvoiceUrlChanged()
            || $changeSet->isExternalCodeChanged()
        ) {
            $this->updateOrder($orderContainer, $changeSet);
        }

        if (
            $changeSet->isInvoiceUrlChanged()
            || $changeSet->isInvoiceNumberChanged()
        ) {
            $this->updateInvoiceDocument($orderContainer->getOrder());
        }

        if (!$orderContainer->getOrder()->wasShipped()) {
            $this->logInfo('Update Order skipped because order was not shipped.');

            return;
        }

        $hasChanges = $changeSet->isAmountChanged()
            || $changeSet->isInvoiceNumberChanged()
            || $changeSet->isInvoiceUrlChanged()
            || $changeSet->isDurationChanged();

        if (!$hasChanges) {
            $this->logInfo('Update Order skipped because there were no amount/duration/document changes.');

            return;
        }

        $invoice = $this->retrieveSingleInvoice($orderContainer);
        if (($invoice !== null) && ($changeSet->isDurationChanged() || $changeSet->isInvoiceNumberChanged())) {
            if ($changeSet->isInvoiceNumberChanged()) {
                $invoice->setExternalCode($changeSet->getInvoiceNumber());
            }

            $duration = $changeSet->getDuration() ?? $invoice->getDuration();
            $this->extendInvoiceService->extend($orderContainer, $invoice, $duration);
        }

        // TODO (partial-shipments): replace borscht modify ticket call with CreateCreditNote msg
        $this->paymentsService->modifyOrder(
            $this->paymentRequestFactory->createModifyRequestDTO($orderContainer)
        );
    }

    private function updateOrder(OrderContainer $orderContainer, LegacyUpdateOrderRequest $changeSet): void
    {
        $order = $orderContainer->getOrder();

        if ($changeSet->isExternalCodeChanged()) {
            $order->setExternalCode($changeSet->getExternalCode());
        }
        if ($changeSet->isInvoiceNumberChanged()) {
            $order->setInvoiceNumber($changeSet->getInvoiceNumber());
        }
        if ($changeSet->isInvoiceUrlChanged()) {
            $order->setInvoiceUrl($changeSet->getInvoiceUrl());
        }
        $this->orderRepository->update($order);
    }

    private function updateInvoiceDocument(OrderEntity $order): void
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
