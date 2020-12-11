<?php

namespace App\DomainModel\OrderResponse;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="OrderResponse", title="Order Entity", type="object", properties={
 *      @OA\Property(property="order_id", type="string", nullable=true, example="C-10123456789-0001"),
 *      @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="state", ref="#/components/schemas/OrderState", example="created"),
 *      @OA\Property(property="reasons", enum=\App\DomainModel\Order\OrderDeclinedReasonsMapper::REASONS, type="string", nullable=true, deprecated=true),
 *      @OA\Property(property="decline_reason", ref="#/components/schemas/OrderDeclineReason", nullable=true),
 *      @OA\Property(property="amount", type="number", format="float", nullable=false, example=123.57, description="Gross amount"),
 *      @OA\Property(property="amount_net", type="number", format="float", nullable=false, example=100.12),
 *      @OA\Property(property="amount_tax", type="number", format="float", nullable=false, example=23.45),
 *      @OA\Property(property="duration", ref="#/components/schemas/OrderDuration", example=30),
 *      @OA\Property(property="dunning_status", ref="#/components/schemas/OrderDunningStatus"),
 *
 *      @OA\Property(property="debtor_company", type="object", description="Identified company", properties={
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", nullable=true, example="Billie GmbH"),
 *          @OA\Property(property="address_house_number", ref="#/components/schemas/TinyText", nullable=true, example="4"),
 *          @OA\Property(property="address_street", ref="#/components/schemas/TinyText", nullable=true, example="Charlottenstr."),
 *          @OA\Property(property="address_postal_code", type="string", nullable=true, maxLength=5, example="10969"),
 *          @OA\Property(property="address_city", ref="#/components/schemas/TinyText", nullable=true, example="Berlin"),
 *          @OA\Property(property="address_country", type="string", nullable=true, maxLength=2),
 *      }),
 *
 *      @OA\Property(property="bank_account", type="object", properties={
 *          @OA\Property(property="iban", ref="#/components/schemas/TinyText", nullable=true, description="Virtual IBAN provided by Billie"),
 *          @OA\Property(property="bic", ref="#/components/schemas/TinyText", nullable=true),
 *      }),
 *
 *      @OA\Property(property="invoice", type="object", properties={
 *          @OA\Property(property="invoice_number", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(property="payout_amount", type="number", format="float", nullable=true),
 *          @OA\Property(property="outstanding_amount", type="number", format="float", nullable=true),
 *          @OA\Property(property="pending_merchant_payment_amount", type="number", format="float", nullable=true),
 *          @OA\Property(property="pending_cancellation_amount", type="number", format="float", nullable=true),
 *          @OA\Property(property="fee_amount", type="number", format="float", nullable=true),
 *          @OA\Property(property="fee_rate", type="number", format="float", nullable=true),
 *          @OA\Property(property="due_date", type="string", format="date", nullable=true, example="2019-03-20"),
 *      }),
 *
 *      @OA\Property(property="debtor_external_data", description="Data provided in the order creation", type="object", properties={
 *          @OA\Property(property="merchant_customer_id", ref="#/components/schemas/TinyText", example="C-10123456789"),
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", example="Billie G.m.b.H."),
 *          @OA\Property(property="address_country", type="string", maxLength=2, example="DE"),
 *          @OA\Property(property="address_city", ref="#/components/schemas/TinyText", example="Berlin"),
 *          @OA\Property(property="address_postal_code", type="string", maxLength=5, example="10969"),
 *          @OA\Property(property="address_street", ref="#/components/schemas/TinyText", example="Charlotten Straße"),
 *          @OA\Property(property="address_house", ref="#/components/schemas/TinyText", example="4"),
 *          @OA\Property(property="industry_sector", ref="#/components/schemas/TinyText", nullable=true),
 *      }),
 *
 *      @OA\Property(property="delivery_address", type="object", ref="#/components/schemas/CreateOrderAddressRequest"),
 *      @OA\Property(property="billing_address", type="object", ref="#/components/schemas/CreateOrderAddressRequest"),
 *      @OA\Property(property="created_at", ref="#/components/schemas/DateTime"),
 *      @OA\Property(property="shipped_at", ref="#/components/schemas/DateTime"),
 *      @OA\Property(property="debtor_uuid", ref="#/components/schemas/UUID"),
 * })
 */
class OrderResponse extends AbstractOrderResponse
{
    private array

 $invoices = [];

    public function getInvoices(): array
    {
        return $this->invoices;
    }

    public function setInvoices(array $invoices): self
    {
        $this->invoices = $invoices;

        return $this;
    }

    public function addInvoice(OrderInvoiceResponse $invoices): self
    {
        $this->invoices[] = $invoices;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            ['invoices' => array_map(fn (OrderInvoiceResponse $invoice) => $invoice->toArray(), $this->invoices)]
        );
    }
}
