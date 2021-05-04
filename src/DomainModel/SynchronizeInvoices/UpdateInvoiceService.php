<?php

namespace App\DomainModel\SynchronizeInvoices;

use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class UpdateInvoiceService implements LoggingInterface
{
    use LoggingTrait;

    private Connection $db;

    private NetTaxCalculatorService $netTaxCalculator;

    public function __construct(Connection $syncConnection, NetTaxCalculatorService $netTaxCalculator)
    {
        $this->db = $syncConnection;
        $this->netTaxCalculator = $netTaxCalculator;
    }

    public function update(OrderSynchronizeWrapper $order): void
    {
        $this->logDebug("Updating the invoice data with order");

        $sql = "UPDATE webapp.invoices SET
            offered_amount = :amount_gross, 
            amount = :amount_gross, 
            billing_date = :billing_date, 
            due_date = :due_date, 
            duration = :duration, 
            payout_date = :payout_date, 
            payout_amount = :payout_amount, 
            factoring_fee_rate = :factoring_fee_rate, 
            outstanding_amount = :outstanding_amount, 
            fee_amount = :fee_amount, 
            fee_vat_amount = :fee_vat_amount,
            fee_net_amount = :fee_net_amount, 
            net_amount = :net_amount,
            customer_debtor_uuid = :debtor_payment_uuid,
            updated_at = :updated_at
            WHERE webapp.invoices.uuid = :uuid
        ";

        $feeVatDate = new \DateTime($order->get('fee_vat_date'));
        $duration = $order->get('duration');

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'uuid' => $order->get('invoice_uuid'),
            'amount_gross' => $order->get('gross_amount'),
            'billing_date' => $order->get('shipped_at'),
            'due_date' => $order->get('due_date'),
            'duration' => $duration,
            'payout_date' => $order->get('payout_date'),
            'payout_amount' => $order->get('payout_amount'),
            'factoring_fee_rate' => $order->get('fee_rate'),
            'outstanding_amount' => $order->get('outstanding_amount'),
            'fee_amount' => $order->get('fee_amount'),
            'fee_vat_amount' => $this->netTaxCalculator->getTax($order->get('fee_amount'), $feeVatDate)->toFloat(),
            'fee_net_amount' => $this->netTaxCalculator->getNet($order->get('fee_amount'), $feeVatDate)->toFloat(),
            'net_amount' => $order->get('net_amount'),
            'debtor_payment_uuid' => $order->get('debtor_payment_uuid'),
            'updated_at' => $order->get('updated_at'),
        ]);

        $this->logDebug("Updating the invoice financing workflow");

        $sql = "UPDATE webapp.invoice_financing_workflows 
            INNER JOIN webapp.invoices ON webapp.invoices.id = webapp.invoice_financing_workflows.invoice_id
            SET
                webapp.invoice_financing_workflows.state = :state, 
                webapp.invoice_financing_workflows.updated_at = :updated_at
            WHERE webapp.invoices.uuid = :invoice_uuid
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'state' => InsertInvoiceService::FINANCING_WORKFLOW_STATE_MAPPINGS[$order->get('state')],
            'invoice_uuid' => $order->get('invoice_uuid'),
            'updated_at' => $order->get('updated_at'),
        ]);
    }
}
