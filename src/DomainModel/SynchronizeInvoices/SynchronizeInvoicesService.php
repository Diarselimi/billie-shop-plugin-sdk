<?php

namespace App\DomainModel\SynchronizeInvoices;

use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

/**
 * @see /src/DomainModel/SynchronizeInvoices/README.md
 */
class SynchronizeInvoicesService implements LoggingInterface
{
    use LoggingTrait;

    private RetrieveOrdersService $retrieveOrdersService;

    private InsertInvoiceService $insertInvoiceService;

    private UpdateInvoiceService $updateInvoiceService;

    private InsertMissingDocumentsService $insertMissingDocumentsService;

    private InsertMissingTransitionsService $insertMissingTransitionsService;

    private Connection $db;

    public function __construct(
        RetrieveOrdersService $retrieveOrdersService,
        InsertInvoiceService $insertInvoiceService,
        UpdateInvoiceService $updateInvoiceService,
        InsertMissingDocumentsService $insertMissingDocumentsService,
        InsertMissingTransitionsService $insertMissingTransitionsService,
        Connection $syncConnection
    ) {
        $this->retrieveOrdersService = $retrieveOrdersService;
        $this->insertInvoiceService = $insertInvoiceService;
        $this->updateInvoiceService = $updateInvoiceService;
        $this->insertMissingDocumentsService = $insertMissingDocumentsService;
        $this->insertMissingTransitionsService = $insertMissingTransitionsService;
        $this->db = $syncConnection;
    }

    public function synchronize(int $firstOrderId, int $limit): void
    {
        $this->logInfo("Starting the synchronization of {$limit} orders from id {$firstOrderId}");

        foreach ($this->retrieveOrdersService->retrieve($firstOrderId, $limit) as $order) {
            $this->logInfo("Processing order {$order->getId()}");

            $this->db->beginTransaction();

            try {
                $this->processOrder($order);
                $this->db->commit();
            } catch (\Exception $exception) {
                $this->db->rollBack();

                throw $exception;
            }

            $this->logDebug("Order {$order->getId()} processed");
        }
    }

    private function processOrder(OrderSynchronizeWrapper $order): void
    {
        if ($order->hasInvoiceInButler()) {
            $this->updateInvoiceService->update($order);
        } else {
            $this->insertInvoiceService->insert($order);
        }

        $this->insertMissingDocumentsService->insertMissing($order);
        $this->insertMissingTransitionsService->insertMissing($order);
    }
}
