<?php

namespace App\Application\UseCase\ShipOrder;

use App\Application\UseCase\AbstractShipOrderRequest;
use App\Application\Validator\Constraint as CustomConstrains;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(schema="ShipOrderRequest", title="Order Shipping Object", type="object",
 *     required={"invoice_url"},
 *     allOf={@OA\Schema(ref="#/components/schemas/AbstractShipOrderRequest")},
 *     properties={
 *      @OA\Property(property="invoice_url", ref="#/components/schemas/URL"),
 *      @OA\Property(property="shipping_document_url", ref="#/components/schemas/URL")
 * })
 */
class ShipOrderRequest extends AbstractShipOrderRequest
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

    /**
     * @Assert\Type(type="integer")
     * @CustomConstrains\OrderDuration()
     */
    private ?int $duration = null;

    public function getInvoiceUrl(): string
    {
        return $this->invoiceUrl;
    }

    public function getShippingDocumentUrl(): ?string
    {
        return $this->shippingDocumentUrl;
    }

    public function setInvoiceUrl(?string $invoiceUrl): ShipOrderRequest
    {
        $this->invoiceUrl = $invoiceUrl;

        return $this;
    }

    public function setShippingDocumentUrl(?string $shippingDocumentUrl): ShipOrderRequest
    {
        $this->shippingDocumentUrl = $shippingDocumentUrl;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): ShipOrderRequest
    {
        $this->duration = $duration;

        return $this;
    }
}
