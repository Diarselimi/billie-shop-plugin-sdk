<?php

declare(strict_types=1);

namespace App\Application\UseCase\UpdateInvoice;

use App\Application\Validator\Constraint\InvoiceUpdate;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="UpdateInvoiceRequest", title="Invoice Update Request",
 *     properties={
 *          @OA\Property(property="external_code", ref="#/components/schemas/TinyText", nullable=true, example="M-0002126", description="Invoice external code which the invoice will be identified by."),
 *          @OA\Property(property="invoice_url", ref="#/components/schemas/TinyText", nullable=true, example="ftp://some_server.com/invoices/M-0002126", description="The url of the generated invoice from merchant.")
 *     }
 * )
 *
 */
class UpdateInvoiceRequest
{
    /**
     * @InvoiceUpdate()
     */
    private ?string $externalCode = null;

    private ?string $invoiceUrl = null;

    private string $invoiceUuid;

    private int $merchantId;

    public function __construct(string $invoiceUuid, int $merchantId)
    {
        $this->invoiceUuid = $invoiceUuid;
        $this->merchantId = $merchantId;
    }

    public function getExternalCode(): ?string
    {
        return $this->externalCode;
    }

    public function setExternalCode(?string $externalCode): UpdateInvoiceRequest
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getInvoiceUrl(): ?string
    {
        return $this->invoiceUrl;
    }

    public function setInvoiceUrl(?string $invoiceUrl): UpdateInvoiceRequest
    {
        $this->invoiceUrl = $invoiceUrl;

        return $this;
    }

    public function getInvoiceUuid(): string
    {
        return $this->invoiceUuid;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }
}
