<?php

namespace App\Application\UseCase\UpdateOrderWithInvoice;

use App\Application\UseCase\AbstractOrderRequest;
use App\Application\UseCase\UpdateOrder\UpdateOrderAmountInterface;
use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(schema="UpdateOrderWithInvoiceRequest", title="Order Update with invoice Object", type="object", properties={
 *      @OA\Property(property="amount", ref="#/components/schemas/AmountDTO"),
 *      @OA\Property(
 *          property="invoice_number",
 *          description="The invoice number. Required when order state is shipped, paid_out or late",
 *          ref="#/components/schemas/TinyText"
 *      ),
 *      @OA\Property(
 *          property="invoice_file",
 *          description="The invoice file. Required when invoice number has changed.",
 *          type="string",
 *          format="binary"
 *      )
 * })
 */
class UpdateOrderWithInvoiceRequest extends AbstractOrderRequest implements
    ValidatedRequestInterface,
    UpdateOrderAmountInterface
{
    /**
     * @Assert\Valid()
     * @var TaxedMoney
     */
    private $amount;

    /**
     * @Assert\NotBlank(groups={"InvoiceNumber"})
     * @Assert\Type(type="string")
     * @Assert\Length(max="255")
     */
    private $invoiceNumber;

    /**
     * @Assert\NotBlank(groups={"InvoiceFile"})
     * @var UploadedFile
     */
    private $invoiceFile;

    public function setAmount(?TaxedMoney $amountRequest): self
    {
        $this->amount = $amountRequest;

        return $this;
    }

    public function getAmount(): ?TaxedMoney
    {
        return $this->amount;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): self
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getInvoiceFile(): ?UploadedFile
    {
        return $this->invoiceFile;
    }

    public function setInvoiceFile(?UploadedFile $invoiceFile): self
    {
        $this->invoiceFile = $invoiceFile;

        return $this;
    }
}
