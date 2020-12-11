<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\OrderInvoice\LegacyOrderInvoiceEntity;
use App\DomainModel\OrderInvoice\LegacyOrderInvoiceRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class LegacyOrderInvoiceRepository extends AbstractPdoRepository implements LegacyOrderInvoiceRepositoryInterface
{
    public const TABLE_NAME = 'order_invoices';

    public function insert(LegacyOrderInvoiceEntity $orderInvoiceEntity): LegacyOrderInvoiceEntity
    {
        $id = $this->doInsert('
            INSERT INTO ' . self::TABLE_NAME . '
            (order_id, file_id, invoice_number, created_at)
            VALUES
            (:order_id, :file_id, :invoice_number, :created_at)
        ', [
            'order_id' => $orderInvoiceEntity->getOrderId(),
            'file_id' => $orderInvoiceEntity->getFileId(),
            'invoice_number' => $orderInvoiceEntity->getInvoiceNumber(),
            'created_at' => $orderInvoiceEntity->getCreatedAt()->format(self::DATE_FORMAT),
        ]);

        $orderInvoiceEntity->setId($id);

        return $orderInvoiceEntity;
    }
}
