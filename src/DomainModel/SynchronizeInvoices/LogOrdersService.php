<?php

namespace App\DomainModel\SynchronizeInvoices;

use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class LogOrdersService implements LoggingInterface
{
    use LoggingTrait;

    private Connection $db;

    public function __construct(Connection $syncConnection)
    {
        $this->db = $syncConnection;
    }

    public function shippedAndUnshipped(int $firstOrderId, int $limit): void
    {
        $sql = "SELECT COUNT(*) AS cnt
            FROM orders 
            WHERE
                orders.shipped_at IS NOT NULL
                AND orders.id >= :first_order_id
            ORDER BY orders.id
            LIMIT 0, {$limit}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'first_order_id' => $firstOrderId,
        ]);
        $count = $stmt->fetch()['cnt'];

        $this->logInfo("Total shipped orders: $count");

        $sql = "SELECT COUNT(*) AS cnt
            FROM orders 
            INNER JOIN order_invoices_v2 ON orders.id = order_invoices_v2.order_id
            WHERE 
                orders.shipped_at IS NOT NULL
                AND orders.id >= :first_order_id
            ORDER BY orders.id
            LIMIT 0, {$limit}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'first_order_id' => $firstOrderId,
        ]);
        $count = $stmt->fetch()['cnt'];

        $this->logInfo("Orders with invoices: $count");
    }
}
