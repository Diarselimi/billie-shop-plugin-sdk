<?php

declare(strict_types=1);

namespace App\Application\UseCase\ShipOrderWithInvoice;

use App\Application\UseCase\AbstractShipOrderRequestV1;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="ShipOrderWithInvoiceRequest", title="Order Shipping With Invoice Object", type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/AbstractShipOrderRequestV1")},
 *     properties={
 *         @OA\Property(property="invoice_file", description="Invoice file", type="string", format="binary"),
 *         @OA\Property(property="amount", ref="#/components/schemas/AmountDTO")
 *     }
 * )
 */
class ShipOrderWithInvoiceRequest extends AbstractShipOrderRequestV1
{
    /**
     * @Assert\NotBlank()
     */
    private $invoiceFile;

    /**
     * @Assert\Valid()
     * @var TaxedMoney
     */
    private $amount;

    public function getInvoiceFile(): UploadedFile
    {
        return $this->invoiceFile;
    }

    public function setInvoiceFile(UploadedFile $invoiceFile): self
    {
        $this->invoiceFile = $invoiceFile;

        return $this;
    }

    public function setAmount(?TaxedMoney $amountRequest): self
    {
        $this->amount = $amountRequest;

        return $this;
    }

    public function getAmount(): ?TaxedMoney
    {
        return $this->amount;
    }

    public function hasAmount(): bool
    {
        return $this->amount !== null;
    }
}
