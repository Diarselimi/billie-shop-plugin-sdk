<?php

namespace App\Application\UseCase\ShipOrder;

use App\Application\UseCase\AbstractShipOrderRequestV1;
use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(schema="ShipOrderRequestV1", title="Order Shipping Object", type="object",
 *     required={"invoice_url"},
 *     allOf={@OA\Schema(ref="#/components/schemas/AbstractShipOrderRequestV1")},
 *     properties={
 *      @OA\Property(property="invoice_url", ref="#/components/schemas/URL"),
 *      @OA\Property(property="shipping_info", ref="#/components/schemas/ShippingInfo", description="Shipping information for tracking, delivery, method."),
 *      @OA\Property(property="shipping_document_url", ref="#/components/schemas/URL", deprecated=true)
 * })
 */
class ShipOrderRequestV1 extends AbstractShipOrderRequestV1 implements ArrayableInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private ?string $invoiceUrl = null;

    /**
     * @Assert\Length(max="255")
     */
    private ?string $shippingDocumentUrl = null;

    private ?UuidInterface $invoiceUuid;

    public function getInvoiceUuid(): ?UuidInterface
    {
        return $this->invoiceUuid;
    }

    public function setInvoiceUuid(?UuidInterface $invoiceUuid): ShipOrderRequestV1
    {
        $this->invoiceUuid = $invoiceUuid;

        return $this;
    }

    public function getInvoiceUrl(): string
    {
        return $this->invoiceUrl;
    }

    public function getShippingDocumentUrl(): ?string
    {
        return $this->shippingDocumentUrl;
    }

    public function setInvoiceUrl(?string $invoiceUrl): ShipOrderRequestV1
    {
        $this->invoiceUrl = $invoiceUrl;

        return $this;
    }

    public function setShippingDocumentUrl(?string $shippingDocumentUrl): ShipOrderRequestV1
    {
        $this->shippingDocumentUrl = $shippingDocumentUrl;

        return $this;
    }

    public function toArray(): array
    {
        $trackingUrl = $this->getShippingInfo() === null ? null : $this->getShippingInfo()->getTrackingUrl();

        return [
            'order_id' => $this->getOrderId(),
            'external_code' => $this->getExternalCode(),
            'merchant_id' => $this->getMerchantId(),
            'invoice_number' => $this->getInvoiceNumber(),
            'invoice_url' => $this->getInvoiceUrl(),
            'shipping_document_url' => $trackingUrl,
        ];
    }
}
