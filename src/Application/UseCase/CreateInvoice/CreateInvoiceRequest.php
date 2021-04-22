<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateInvoice;

use OpenApi\Annotations as OA;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(schema="CreateInvoiceRequest", title="Create Invoice Request", required={"orders", "external_code", "invoice_url", "amount"},
 *     properties={
 *          @OA\Property(
 *              property="orders",
 *              type="array",
 *              nullable=false,
 *              @OA\Items(ref="#/components/schemas/TinyText")
 *          ),
 *          @OA\Property(property="external_code", ref="#/components/schemas/TinyText", nullable=false),
 *          @OA\Property(property="invoice_url", ref="#/components/schemas/TinyText", nullable=false),
 *          @OA\Property(property="shipping_document_url", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(property="amount", ref="#/components/schemas/AmountDTO", nullable=false),
 *          @OA\Property(
 *              property="line_items",
 *              type="array",
 *              nullable=true,
 *              @OA\Items(ref="#/components/schemas/LineItemsRequest")
 *          )
 *     }
 * )
 */
class CreateInvoiceRequest
{
    private ?int $merchantId = null;

    /**
     * @Assert\Count(
     *     min=1,
     *     minMessage="There should be at least one order specified."
     * )
     */
    private array

 $orders;

    /**
     * @Assert\Length(max="255")
     */
    private ?string $externalCode = null;

    /**
     * @Assert\Length(max="255")
     */
    private ?string $invoiceUrl = null;

    /**
     * @Assert\Length(max="255")
     */
    private ?string $shippingDocumentUrl = null;

    /**
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private ?TaxedMoney $amount = null;

    private array

 $lineItems;

    public function __construct(int $merchantId)
    {
        $this->merchantId = $merchantId;
    }

    public function getOrders(): array
    {
        return $this->orders;
    }

    public function setOrders(array $orders): CreateInvoiceRequest
    {
        $this->orders = $orders;

        return $this;
    }

    public function getExternalCode(): ?string
    {
        return $this->externalCode;
    }

    public function setExternalCode(?string $externalCode): CreateInvoiceRequest
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getInvoiceUrl(): ?string
    {
        return $this->invoiceUrl;
    }

    public function setInvoiceUrl(?string $invoiceUrl): CreateInvoiceRequest
    {
        $this->invoiceUrl = $invoiceUrl;

        return $this;
    }

    public function getAmount(): ?TaxedMoney
    {
        return $this->amount;
    }

    public function setAmount(?TaxedMoney $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getShippingDocumentUrl(): ?string
    {
        return $this->shippingDocumentUrl;
    }

    public function setShippingDocumentUrl(?string $shippingDocumentUrl): CreateInvoiceRequest
    {
        $this->shippingDocumentUrl = $shippingDocumentUrl;

        return $this;
    }

    public function getLineItems(): array
    {
        return $this->lineItems;
    }

    public function setLineItems(array $lineItems): CreateInvoiceRequest
    {
        $this->lineItems = $lineItems;

        return $this;
    }

    public function getMerchantId(): ?int
    {
        return $this->merchantId;
    }
}
