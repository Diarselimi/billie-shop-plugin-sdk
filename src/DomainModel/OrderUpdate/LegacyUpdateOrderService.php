<?php

namespace App\DomainModel\OrderUpdate;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\LegacyUpdateOrder\LegacyUpdateOrderRequest;
use App\DomainModel\Fee\FeeCalculationException;
use App\DomainModel\Invoice\CreditNote\CreditNote;
use App\DomainModel\Invoice\CreditNote\CreditNoteFactory;
use App\DomainModel\Invoice\CreditNote\InvoiceCreditNoteMessageFactory;
use App\DomainModel\Invoice\ExtendInvoiceService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsPersistenceService;
use App\DomainModel\OrderInvoiceDocument\InvoiceDocumentUploadException;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\InvoiceDocumentUploadHandlerAggregator;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\InvoiceDocumentUploadHandlerInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Messenger\MessageBusInterface;

class LegacyUpdateOrderService implements LoggingInterface
{
    use LoggingTrait;

    private OrderRepositoryInterface $orderRepository;

    private InvoiceDocumentUploadHandlerAggregator $invoiceUrlHandler;

    private OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService;

    private UpdateOrderLimitsService $updateOrderLimitsService;

    private UpdateOrderRequestValidator $updateOrderRequestValidator;

    private ExtendInvoiceService $extendInvoiceService;

    private InvoiceCreditNoteMessageFactory $creditNoteMessageFactory;

    private CreditNoteFactory $creditNoteFactory;

    private MessageBusInterface $bus;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        InvoiceDocumentUploadHandlerAggregator $invoiceUrlHandler,
        UpdateOrderLimitsService $updateOrderLimitsService,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        ExtendInvoiceService $extendInvoiceService,
        InvoiceCreditNoteMessageFactory $creditNoteAnnouncer,
        CreditNoteFactory $creditNoteFactory,
        MessageBusInterface $bus
    ) {
        $this->orderRepository = $orderRepository;
        $this->financialDetailsPersistenceService = $financialDetailsPersistenceService;
        $this->invoiceUrlHandler = $invoiceUrlHandler;
        $this->updateOrderLimitsService = $updateOrderLimitsService;
        $this->updateOrderRequestValidator = $updateOrderRequestValidator;
        $this->extendInvoiceService = $extendInvoiceService;
        $this->creditNoteMessageFactory = $creditNoteAnnouncer;
        $this->creditNoteFactory = $creditNoteFactory;
        $this->bus = $bus;
    }

    public function update(OrderContainer $orderContainer, LegacyUpdateOrderRequest $request): LegacyUpdateOrderRequest
    {
        $order = $orderContainer->getOrder();
        if ($order->isWorkflowV2()) {
            throw new WorkflowException('Order workflow v2 is not supported by API v1');
        }
        $changeSet = $this->updateOrderRequestValidator->getValidatedRequest($orderContainer, $request);

        $this->logChangeSet($orderContainer, $changeSet);

        if ($changeSet->isAmountChanged() && !$order->wasShipped()) {
            $this->updateOrderLimitsService->updateLimitAmounts($orderContainer, $changeSet->getAmount());
        }

        $this->doUpdate($orderContainer, $changeSet);

        return $changeSet;
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

    private function doUpdate(OrderContainer $orderContainer, LegacyUpdateOrderRequest $changeSet): void
    {
        $invoice = $orderContainer->getInvoices()->getLastInvoice();

        if ($changeSet->isAmountChanged() || $changeSet->isDurationChanged()) {
            $changedAmount = $changeSet->getAmount();
            $duration = $changeSet->isDurationChanged()
                ? $changeSet->getDuration()
                : $orderContainer->getOrderFinancialDetails()->getDuration()
            ;

            if (!$orderContainer->getInvoices()->isEmpty()) {
                if ($invoice !== null && $changeSet->isInvoiceNumberChanged()) {
                    $invoice->setExternalCode($changeSet->getInvoiceNumber());
                }
                $this->onAmountChangeDispatchCreditNoteMessage($changeSet, $orderContainer);
                $changeSet->setAmount(null);
            }

            $this->financialDetailsPersistenceService->updateFinancialDetails(
                $orderContainer,
                $changeSet,
                $duration
            );

            $changeSet->setAmount($changedAmount);
        }

        if (
            $changeSet->isInvoiceNumberChanged()
            || $changeSet->isInvoiceUrlChanged()
            || $changeSet->isExternalCodeChanged()
        ) {
            $this->updateOrder($orderContainer, $changeSet);
        }

        if ($changeSet->isInvoiceUrlChanged()) {
            $this->updateInvoiceDocument($orderContainer->getOrder());
        }

        if (!$orderContainer->getOrder()->wasShipped()) {
            $this->logInfo('Update Order skipped because order was not shipped.');

            return;
        }

        $hasChanges = $changeSet->isInvoiceNumberChanged()
            || $changeSet->isInvoiceUrlChanged()
            || $changeSet->isDurationChanged();

        if (!$hasChanges) {
            $this->logInfo('Update Order skipped because there were no amount/duration/document changes.');

            return;
        }

        if ($changeSet->isDurationChanged() || $changeSet->isInvoiceNumberChanged()) {
            if ($changeSet->isInvoiceNumberChanged()) {
                $invoice->setExternalCode($changeSet->getInvoiceNumber());
            }
            $duration = $changeSet->getDuration() ?? $invoice->getDuration();
            if ($changeSet->isDurationChanged()) {
                sleep(1);
            }

            try {
                $this->extendInvoiceService->extend($orderContainer, $invoice, $duration);
            } catch (FeeCalculationException $exception) {
                throw new UpdateOrderException("Order cannot be updated: fee calculation failed.", null, $exception);
            }
        }
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

    private function calculateReducedAmount(
        OrderContainer $orderContainer,
        LegacyUpdateOrderRequest $changeSet
    ): TaxedMoney {
        $initialAmountGross = $orderContainer->getOrderFinancialDetails()->getAmountGross();
        $initialAmountNet = $orderContainer->getOrderFinancialDetails()->getAmountNet();

        $reducedAmountGross = $initialAmountGross
            ->subtract($orderContainer->getInvoices()->getInvoicesCreditNotesGrossSum())
            ->subtract($changeSet->getAmount()->getGross());
        $reducedAmountNet = $initialAmountNet
            ->subtract($orderContainer->getInvoices()->getInvoicesCreditNotesNetSum())
            ->subtract($changeSet->getAmount()->getNet());

        return new TaxedMoney($reducedAmountGross, $reducedAmountNet, $reducedAmountGross->subtract($reducedAmountNet));
    }

    private function onAmountChangeDispatchCreditNoteMessage(
        LegacyUpdateOrderRequest $changeSet,
        OrderContainer $orderContainer
    ): void {
        if (!$changeSet->isAmountChanged()) {
            return;
        }

        $invoice = $orderContainer->getInvoices()->getLastInvoice();

        $differenceAmount = $this->calculateReducedAmount($orderContainer, $changeSet);
        $creditNote = $this->creditNoteFactory->create(
            $invoice,
            $differenceAmount,
            $invoice->getExternalCode() . CreditNote::EXTERNAL_CODE_SUFFIX,
            null
        );

        $this->bus->dispatch($this->creditNoteMessageFactory->create($creditNote));
        $invoice->getCreditNotes()->add($creditNote);
    }
}
