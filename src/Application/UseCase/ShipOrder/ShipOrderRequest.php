<?php

namespace App\Application\UseCase\ShipOrder;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\ShipOrder\AbstractShipOrderRequest;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="ShipOrderRequest", title="Order Shipping Object", type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/AbstractShipOrderRequest")},
 *     properties={
 *      @OA\Property(property="invoice_url", ref="#/components/schemas/URL"),
 *      @OA\Property(property="shipping_document_url", ref="#/components/schemas/URL")
 * })
 */
class ShipOrderRequest extends AbstractShipOrderRequest implements ArrayableInterface
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private $invoiceUrl;

    /**
     * @var string
     * @Assert\Length(max="255")
     */
    private $shippingDocumentUrl;

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

    public function toArray(): array
    {
        return [
            'order_id' => $this->getOrderId(),
            'external_code' => $this->getExternalCode(),
            'merchant_id' => $this->getMerchantId(),
            'invoice_number' => $this->getInvoiceNumber(),
            'invoice_url' => $this->getInvoiceUrl(),
            'shipping_document_url' => $this->getShippingDocumentUrl(),
        ];
    }
}
