<?php

namespace App\DomainModel\SynchronizeInvoices;

use App\Helper\Uuid\UuidGeneratorInterface;
use App\Support\RandomStringGenerator;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class InsertInvoiceService implements LoggingInterface
{
    use LoggingTrait;

    private Connection $db;

    private UuidGeneratorInterface $uuidGenerator;

    private RandomStringGenerator $randomStringGenerator;

    private NetTaxCalculatorService $netTaxCalculator;

    public const FINANCING_WORKFLOW_STATE_MAPPINGS = [
        'shipped' => 'new',
        'paid_out' => 'paid_out',
        'late' => 'paid_out',
        'canceled' => 'canceled',
        'complete' => 'complete',
    ];

    public function __construct(
        Connection $syncConnection,
        UuidGeneratorInterface $uuidGenerator,
        RandomStringGenerator $randomStringGenerator,
        NetTaxCalculatorService $netTaxCalculator
    ) {
        $this->db = $syncConnection;
        $this->uuidGenerator = $uuidGenerator;
        $this->randomStringGenerator = $randomStringGenerator;
        $this->netTaxCalculator = $netTaxCalculator;
    }

    public function insert(OrderSynchronizeWrapper $order): void
    {
        $this->createInvoice($order);
        $this->createFinancingWorkflow($order);
        $this->linkOrderToInvoice($order);
    }

    private function createInvoice(OrderSynchronizeWrapper $order): void
    {
        $sql = "INSERT INTO webapp.invoices (
            invoice_external_nr, 
            invoice_code, 
            offered_amount, 
            amount, 
            billing_date, 
            due_date, 
            state, 
            uuid, 
            payout_date, 
            duration, 
            payout_amount, 
            factoring_fee_rate, 
            outstanding_amount, 
            created_at, 
            updated_at, 
            fee_amount, 
            fee_vat_amount,
            fee_net_amount, 
            net_amount, 
            proof_of_delivery_url, 
            payment_uuid, 
            customer_company_uuid, 
            customer_debtor_uuid,
            debtor_company_uuid
        ) VALUES (
	        :invoice_number,
	        :invoice_code,
	        :amount_gross,
	        :amount_gross,
	        :billing_date,
	        :due_date,
	        :state,
	        :uuid,
	        :payout_date, 
            :duration, 
            :payout_amount, 
            :factoring_fee_rate, 
            :outstanding_amount, 
            :created_at, 
            :updated_at, 
            :fee_amount, 
            :fee_vat_amount,
            :fee_net_amount, 
            :net_amount, 
            :proof_of_delivery_url, 
            :payment_uuid, 
            :customer_company_uuid, 
            :debtor_payment_uuid,
            :debtor_company_uuid
        )";

        $feeVatDate = new \DateTime($order->get('fee_vat_date'));
        $duration = $order->get('duration');

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'merchant_debtor_uuid' => $order->get('debtor_payment_uuid'),
            'invoice_number' => $order->get('invoice_number'),
            'invoice_code' => $this->randomStringGenerator->generateFromCharList('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 8),
            'amount_gross' => $order->get('gross_amount'),
            'billing_date' => $order->get('shipped_at'),
            'due_date' => $order->get('due_date'),
            'state' => '',
            'uuid' => $order->get('payment_id'),
            'payout_date' => $order->get('payout_date'),
            'duration' => $duration,
            'payout_amount' => $order->get('payout_amount'),
            'factoring_fee_rate' => $order->get('fee_rate'),
            'outstanding_amount' => $order->get('outstanding_amount'),
            'created_at' => $order->get('shipped_at'),
            'updated_at' => $order->get('updated_at'),
            'fee_amount' => $order->get('fee_amount'),
            'fee_vat_amount' => $this->netTaxCalculator->getTax($order->get('fee_amount'), $feeVatDate)->toFloat(),
            'fee_net_amount' => $this->netTaxCalculator->getNet($order->get('fee_amount'), $feeVatDate)->toFloat(),
            'net_amount' => $order->get('net_amount'),
            'proof_of_delivery_url' => $order->get('proof_of_delivery_url'),
            'payment_uuid' => $order->get('payment_id'),
            'customer_company_uuid' => $order->get('merchant_payment_uuid'),
            'debtor_payment_uuid' => $order->get('debtor_payment_uuid'),
            'debtor_company_uuid' => $order->get('debtor_company_uuid'),
        ]);

        $order->set('invoice_id', $id = $this->db->lastInsertId());
        $order->set('invoice_uuid', $order->get('payment_id'));

        $this->logDebug("Created the invoice {$id}");
    }

    private function createFinancingWorkflow(OrderSynchronizeWrapper $order): void
    {
        $sql = "INSERT INTO webapp.invoice_financing_workflows 
            (invoice_id, uuid, state, created_at, updated_at)
            VALUES
            (:invoice_id, :uuid, :state, :created_at, :updated_at)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'invoice_id' => $order->get('invoice_id'),
            'uuid' => $this->uuidGenerator->uuid4(),
            'state' => self::FINANCING_WORKFLOW_STATE_MAPPINGS[$order->get('state')],
            'created_at' => $order->get('shipped_at'),
            'updated_at' => $order->get('shipped_at'), // do we care?
        ]);

        $order->set('invoice_financing_workflow_id', $id = $this->db->lastInsertId());

        $this->logDebug("Created the financing workflow {$id}");
    }

    private function linkOrderToInvoice(OrderSynchronizeWrapper $order): void
    {
        $sql = "INSERT INTO order_invoices_v2
            (order_id, invoice_uuid, created_at)
            VALUES
            (:order_id, :invoice_uuid, :created_at)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'order_id' => $order->getId(),
            'invoice_uuid' => $order->get('payment_id'),
            'created_at' => $order->get('shipped_at'),
        ]);

        $order->set('order_invoice_v2_id', $id = $this->db->lastInsertId());

        $this->logDebug("Order<>Invoice link {$id} created");
    }
}
