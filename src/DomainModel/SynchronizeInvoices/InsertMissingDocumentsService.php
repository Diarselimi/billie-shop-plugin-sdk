<?php

namespace App\DomainModel\SynchronizeInvoices;

use App\Helper\Uuid\UuidGeneratorInterface;
use App\Support\RandomStringGenerator;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class InsertMissingDocumentsService implements LoggingInterface
{
    use LoggingTrait;

    private const DOCUMENT_TYPE = 'invoice';

    private Connection $db;

    private UuidGeneratorInterface $uuidGenerator;

    private RandomStringGenerator $randomStringGenerator;

    public function __construct(Connection $syncConnection, UuidGeneratorInterface $uuidGenerator, RandomStringGenerator $randomStringGenerator)
    {
        $this->db = $syncConnection;
        $this->uuidGenerator = $uuidGenerator;
        $this->randomStringGenerator = $randomStringGenerator;
    }

    public function insertMissing(OrderSynchronizeWrapper $order): void
    {
        $sql = "SELECT order_invoices.invoice_number as external_code, nachos.files.uuid as file_uuid
            FROM order_invoices
            INNER JOIN nachos.files ON nachos.files.id =  order_invoices.file_id
            LEFT JOIN webapp.documents ON webapp.documents.file_uuid = nachos.files.uuid
            WHERE order_invoices.order_id = :order_id AND webapp.documents.id IS NULL
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'order_id' => $order->getId(),
        ]);

        $this->logDebug("Found {$stmt->rowCount()} missing documents");

        while (($document = $stmt->fetch()) !== false) {
            $this->insert($order, $document['external_code'], $document['file_uuid']);
        }
    }

    private function insert(OrderSynchronizeWrapper $order, string $externalCode, string $fileUuid): void
    {
        $sql = "INSERT INTO webapp.documents (invoice_id, type, uuid, created_at, updated_at, code, external_code, file_uuid)
            SELECT webapp.invoices.id, :type, :uuid, :shipped_at, :shipped_at, :code, :external_code, :file_uuid
            FROM webapp.invoices WHERE webapp.invoices.uuid = :invoice_uuid  
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'invoice_uuid' => $order->get('invoice_uuid'),
            'type' => self::DOCUMENT_TYPE,
            'uuid' => $this->uuidGenerator->uuid4(),
            'shipped_at' => $order->get('shipped_at'),
            'code' => $this->randomStringGenerator->generateFromCharList('ABCDEFGHIJKLMNOPQRSTUVWXYZ', 10),
            'external_code' => $externalCode,
            'file_uuid' => $fileUuid,
        ]);

        $id = $this->db->lastInsertId();
        $this->logDebug("Created new document {$id}");
    }
}
