<?php

namespace App\DomainModel\SynchronizeInvoices;

use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class InsertMissingTransitionsService implements LoggingInterface
{
    use LoggingTrait;

    private Connection $db;

    public function __construct(Connection $syncConnection)
    {
        $this->db = $syncConnection;
    }

    public function insertMissing(OrderSynchronizeWrapper $order): void
    {
        $sql = "
            INSERT INTO webapp.invoice_financing_workflow_transitions (object_id, from_state, to_state, transition, transited_at)
            SELECT 
                webapp.invoice_financing_workflows.id, 
                CASE 
                    WHEN order_transitions.from = 'shipped' THEN 'new'
                    WHEN order_transitions.from = 'late' THEN 'paid_out'
                    ELSE order_transitions.from
                END,
                order_transitions.to,
                order_transitions.transition,
                order_transitions.transited_at
            FROM webapp.invoices
            INNER JOIN webapp.invoice_financing_workflows ON webapp.invoice_financing_workflows.invoice_id = webapp.invoices.id
            INNER JOIN paella.order_invoices_v2 ON webapp.invoices.uuid = paella.order_invoices_v2.invoice_uuid
            INNER JOIN paella.order_transitions ON paella.order_transitions.order_id = paella.order_invoices_v2.order_id AND paella.order_transitions.transition IN ('pay_out', 'cancel', 'complete')
            WHERE 
                webapp.invoices.uuid = :invoice_uuid
                AND NOT EXISTS (
                    SELECT * FROM webapp.invoice_financing_workflow_transitions transitions_2
                    WHERE transitions_2.object_id = webapp.invoice_financing_workflows.id AND transitions_2.transition = order_transitions.transition AND transitions_2.transited_at = order_transitions.transited_at
                )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'invoice_uuid' => $order->get('invoice_uuid'),
        ]);

        $this->logDebug("Inserted {$stmt->rowCount()} missing workflow transitions");
    }
}
