<?php

declare(strict_types=1);

namespace App\Infrastructure\InvoiceButler;

use App\DomainModel\Fee\Fee;
use App\DomainModel\Invoice\CreditNote\CreditNote;
use App\DomainModel\Invoice\CreditNote\InvoiceCreditNoteMessageFactory;
use App\DomainModel\Invoice\Duration;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Invoice\InvoiceFactory;
use App\DomainModel\Invoice\InvoiceServiceException;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Payment\PaymentRequestFactory;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\Infrastructure\DecodeResponseTrait;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;
use Ozean12\Transfer\Message\Invoice\ExtendInvoice;
use Ozean12\Transfer\Shared\Invoice as InvoiceMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class InvoiceButlerClient implements InvoiceServiceInterface, LoggingInterface
{
    use LoggingTrait, DecodeResponseTrait;

    private Client $client;

    private InvoiceFactory $invoiceFactory;

    private InvoiceCreditNoteMessageFactory $creditNoteMessageFactory;

    private MessageBusInterface $messageBus;

    private PaymentRequestFactory $paymentRequestFactory;

    private PaymentsServiceInterface $paymentsService;

    public function __construct(
        Client $invoiceButlerClient,
        InvoiceFactory $invoiceFactory,
        InvoiceCreditNoteMessageFactory $creditNoteMessageFactory,
        MessageBusInterface $messageBus,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentsServiceInterface $paymentsService
    ) {
        $this->client = $invoiceButlerClient;
        $this->invoiceFactory = $invoiceFactory;
        $this->creditNoteMessageFactory = $creditNoteMessageFactory;
        $this->messageBus = $messageBus;
        $this->paymentRequestFactory = $paymentRequestFactory;
        $this->paymentsService = $paymentsService;
    }

    public function getByUuids(array $uuids): InvoiceCollection
    {
        try {
            $response = $this->client->get(
                'invoices',
                [
                    'query' => ['uuids' => $uuids],
                    'on_stats' => function (TransferStats $stats) {
                        $this->logServiceRequestStats($stats, 'get_invoices');
                    },
                ]
            );

            $invoices = $this->invoiceFactory->createFromArrayCollection(
                $this->decodeResponse($response)
            );

            return new InvoiceCollection($invoices);
        } catch (ClientException | TransferException $exception) {
            throw new InvoiceServiceException($exception);
        }
    }

    public function getOneByUuid(string $uuid): ?Invoice
    {
        $invoices = $this->getByUuids([$uuid]);

        if ($invoices->isEmpty()) {
            return null;
        }

        return $invoices->getFirst();
    }

    public function createCreditNote(Invoice $invoice, CreditNote $creditNote): void
    {
        // TODO remove next line & dependencies when invoice-butler CreditNote use case is ready:
        $this->handleCreditNoteWithBorscht($invoice, $creditNote);

        $creditNoteMessage = $this->creditNoteMessageFactory->create($creditNote);
        $this->messageBus->dispatch($creditNoteMessage);
    }

    private function handleCreditNoteWithBorscht(Invoice $invoice, CreditNote $creditNote): void
    {
        $creditNotesSum = $invoice->getCreditNotes()->getGrossSum();
        $isFullCancellation = $creditNotesSum->add($creditNote->getAmount()->getGross())
            ->equals($invoice->getAmount()->getGross());

        $logData = [
            LoggingInterface::KEY_SOBAKA => [
                'invoice_uuid' => $invoice->getUuid(),
                'invoice_outstanding_amount' => $invoice->getOutstandingAmount()->getMoneyValue(),
                'invoice_amount' => $invoice->getAmount()->getGross(),
                'credit_notes_sum' => $creditNotesSum->getMoneyValue(),
                'credit_note_amount' => $creditNote->getAmount()->getGross()->getMoneyValue(),
            ],
        ];

        if ($isFullCancellation) {
            $this->logInfo(
                'The invoice will be FULLY cancelled (invoice_cancellation obligation should be created)',
                $logData
            );
            $order = new OrderEntity();
            $order->setPaymentId($invoice->getPaymentUuid());
            // this just cancels the borscht ticket for this invoice, not the boost order!
            $this->paymentsService->cancelOrder($order);

            return;
        }

        $this->logInfo(
            'The invoice will be PARTIALLY cancelled (invoice_payback obligation amount should be reduced)',
            $logData
        );
        $modifyTicketRequest = $this->paymentRequestFactory->createModifyRequestFromInvoice(
            $invoice,
            $creditNote->getAmount()->getGross()
        );
        $this->paymentsService->modifyOrder($modifyTicketRequest);
    }

    public function extendInvoiceDuration(Invoice $invoice, Fee $fee, Duration $duration): void
    {
        $newBillingDate = $duration->addToDate($invoice->getBillingDate());
        $invoiceMessage = (new InvoiceMessage())
            ->setUuid($invoice->getUuid())
            ->setDueDate($newBillingDate->format('Y-m-d'))
            ->setFeeRate($fee->getFeeRate()->shift(2)->toInt())
            ->setNetFeeAmount($fee->getNetFeeAmount()->shift(2)->toInt())
            ->setVatOnFeeAmount($fee->getTaxFeeAmount()->shift(2)->toInt())
            ->setDuration($duration->days())
            ->setInvoiceReferences(
                ['external_code' => $invoice->getExternalCode()]
            );

        $extendInvoiceMessage = (new ExtendInvoice())
            ->setInvoice($invoiceMessage);

        $this->messageBus->dispatch($extendInvoiceMessage);
    }
}
