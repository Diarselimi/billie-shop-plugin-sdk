<?php

declare(strict_types=1);

namespace App\Infrastructure\InvoiceButler;

use App\DomainModel\Invoice\CreditNote\CreditNote;
use App\DomainModel\Invoice\CreditNote\InvoiceCreditNoteMessageFactory;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Invoice\InvoiceFactory;
use App\DomainModel\Invoice\InvoiceServiceException;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\Infrastructure\DecodeResponseTrait;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;
use Ozean12\Transfer\Message\Invoice\ExtendInvoice;
use Symfony\Component\Messenger\MessageBusInterface;

class InvoiceButlerClient implements InvoiceServiceInterface, LoggingInterface
{
    use LoggingTrait, DecodeResponseTrait;

    private Client $client;

    private InvoiceFactory $invoiceFactory;

    private InvoiceCreditNoteMessageFactory $creditNoteMessageFactory;

    private InvoiceMessageFactory $invoiceMessageFactory;

    private MessageBusInterface $messageBus;

    public function __construct(
        Client $invoiceButlerClient,
        InvoiceFactory $invoiceFactory,
        InvoiceCreditNoteMessageFactory $creditNoteMessageFactory,
        InvoiceMessageFactory $invoiceMessageFactory,
        MessageBusInterface $messageBus
    ) {
        $this->client = $invoiceButlerClient;
        $this->invoiceFactory = $invoiceFactory;
        $this->creditNoteMessageFactory = $creditNoteMessageFactory;
        $this->invoiceMessageFactory = $invoiceMessageFactory;
        $this->messageBus = $messageBus;
    }

    public function getByParameters(array $parameters): InvoiceCollection
    {
        try {
            $response = $this->client->get(
                'invoices',
                [
                    'query' => $parameters,
                    'on_stats' => function (TransferStats $stats) {
                        $this->logServiceRequestStats($stats, 'get_invoices');
                    },
                ]
            );

            $invoices = $this->invoiceFactory->createFromArrayMultiple(
                $this->decodeResponse($response)
            );

            return new InvoiceCollection($invoices);
        } catch (ClientException | TransferException $exception) {
            throw new InvoiceServiceException($exception);
        }
    }

    public function getOneByUuid(string $uuid): ?Invoice
    {
        $invoices = $this->getByParameters(['uuids' => [$uuid]]);

        if ($invoices->isEmpty()) {
            return null;
        }

        return $invoices->getFirst();
    }

    public function getByUuids(array $uuids): InvoiceCollection
    {
        return $this->getByParameters(['uuids' => $uuids]);
    }

    public function createCreditNote(Invoice $invoice, CreditNote $creditNote): void
    {
        $creditNoteMessage = $this->creditNoteMessageFactory->create($creditNote);
        $this->messageBus->dispatch($creditNoteMessage);
    }

    public function extendInvoiceDuration(Invoice $invoice): void
    {
        $invoiceMessage = $this->invoiceMessageFactory->create($invoice);
        $extendInvoiceMessage = (new ExtendInvoice())
            ->setInvoice($invoiceMessage)
        ;

        $this->messageBus->dispatch($extendInvoiceMessage);
    }
}
