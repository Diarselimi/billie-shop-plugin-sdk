<?php

namespace App\DomainModel\SynchronizeInvoices;

use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class RetrieveOrdersService implements LoggingInterface
{
    use LoggingTrait;

    private Connection $db;

    public function __construct(Connection $syncConnection)
    {
        $this->db = $syncConnection;
    }

    /**
     * @return \Generator|OrderSynchronizeWrapper[]
     */
    public function retrieve(int $firstOrderId, int $limit): \Generator
    {
        $batchOffset = 0;
        $batchLimit = min(100, $limit);

        while ($batchOffset < $limit) {
            $this->logInfo("Retrieving batch {$batchOffset}, {$batchLimit}");
            $sql = "SELECT 
                orders.id, 
                orders.invoice_number,
                orders.shipped_at,
                orders.updated_at,
                orders.state as state,
                order_invoices_v2.invoice_uuid as invoice_uuid,
                orders.payment_id,
                orders.proof_of_delivery_url,
                merchants_debtors.payment_debtor_id AS debtor_payment_uuid,
                merchants_debtors.company_uuid AS debtor_company_uuid,
                merchants.payment_merchant_id as merchant_payment_uuid,
                order_financial_details.amount_gross as gross_amount,
                order_financial_details.amount_net as net_amount,
                order_financial_details.duration,
                tickets.payout_date,
                tickets.payout_amount,
                tickets.due_date,
                tickets.fee_rate,
                tickets.fee_vat_date,
                tickets.fee_amount,
                tickets.outstanding_amount,
                tickets.fee_vat_date
            FROM orders 
            INNER JOIN merchants ON merchants.id = orders.merchant_id
            INNER JOIN merchants_debtors ON merchants_debtors.id = orders.merchant_debtor_id
            INNER JOIN order_financial_details ON order_financial_details.order_id = orders.id
            INNER JOIN (SELECT MAX(id) AS maxID FROM paella.order_financial_details GROUP BY order_id) AS ofd ON ofd.maxID = order_financial_details.id
            INNER JOIN borscht.tickets tickets ON borscht.tickets.uuid = orders.payment_id
            LEFT JOIN order_invoices_v2 ON orders.id = order_invoices_v2.order_id
            WHERE 
                orders.shipped_at IS NOT NULL
                AND orders.id >= :first_order_id
            ORDER BY orders.id
            LIMIT {$batchOffset}, {$batchLimit}
        ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'first_order_id' => $firstOrderId,
            ]);

            while (($order = $stmt->fetch()) !== false) {
                $json = json_encode($order);
                $this->logDebug("Yield order {$json}");
                yield $this->wrapOrder($order);
            }

            $batchOffset += 100;
            $batchLimit = min(100, $limit - $batchOffset);
        }
    }

    private function wrapOrder(array $data): OrderSynchronizeWrapper
    {
        return new OrderSynchronizeWrapper($data);
    }
}
