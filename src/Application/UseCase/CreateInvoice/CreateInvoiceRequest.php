<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateInvoice;

use App\Application\Validator\Constraint as CustomConstrains;
use App\DomainModel\Invoice\InvoiceRequest;
use OpenApi\Annotations as OA;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(schema="CreateInvoiceRequest", title="Create Invoice Request", required={"orders", "external_code", "invoice_url", "amount"},
 *     properties={
 *          @OA\Property(
 *              property="orders",
 *              type="array",
 *              nullable=false,
 *              @OA\Items(ref="#/components/schemas/UUID"),
 *              description="Include all the order uuids or external codes that you want to create an invoice for (currently only one order is supported)."
 *          ),
 *          @OA\Property(property="external_code", ref="#/components/schemas/TinyText", nullable=false, example="M-0002126", description="External code for the invoice to be identified by."),
 *          @OA\Property(property="invoice_url", ref="#/components/schemas/TinyText", example="ftp://some_server.com/invoices/M-0002126", description="The url of the generated invoice from merchant."),
 *          @OA\Property(property="shipping_document_url", ref="#/components/schemas/TinyText", example="TRACK-0002126", description="The tracking url of the shipping."),
 *          @OA\Property(property="amount", ref="#/components/schemas/AmountDTO", nullable=false, description="The amount of the Invoice in Gross, Net, Tax."),
 *          @OA\Property(
 *              property="line_items",
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/LineItemsRequest")
 *          )
 *     }
 * )
 */
class CreateInvoiceRequest implements InvoiceRequest
{
    private ?int $merchantId = null;

    /**
     * @Assert\Count(
     *     min=1,
     *     minMessage="There should be at least one order specified."
     * )
     */
    private array $orders;

    private UuidInterface $invoiceUuid;

    /**
     * @Assert\Length(max="255")
     * @CustomConstrains\InvoiceExternalCode()
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

    private ?array $lineItems;

    private ?\DateTimeInterface $billingDate;

    public function __construct(int $merchantId, UuidInterface $invoiceUuid)
    {
        $this->merchantId = $merchantId;
        $this->invoiceUuid = $invoiceUuid;
        $this->billingDate = new \DateTime();
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

    public function getExternalCode(): string
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

    public function getAmount(): TaxedMoney
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

    public function setLineItems(?array $lineItems): CreateInvoiceRequest
    {
        $this->lineItems = $lineItems;

        return $this;
    }

    public function getMerchantId(): ?int
    {
        return $this->merchantId;
    }

    public function getBillingDate(): \DateTimeInterface
    {
        return $this->billingDate;
    }

    public function getInvoiceUuid(): UuidInterface
    {
        return $this->invoiceUuid;
    }
}
