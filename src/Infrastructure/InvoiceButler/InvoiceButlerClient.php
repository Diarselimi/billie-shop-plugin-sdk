<?php

declare(strict_types=1);

namespace App\Infrastructure\InvoiceButler;

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

class InvoiceButlerClient implements InvoiceServiceInterface, LoggingInterface
{
    use LoggingTrait, DecodeResponseTrait;

    private Client $client;

    private InvoiceFactory $factory;

    public function __construct(Client $invoiceButlerClient, InvoiceFactory $factory)
    {
        $this->client = $invoiceButlerClient;
        $this->factory = $factory;
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

            $invoices = $this->factory->createFromArrayCollection(
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
}
