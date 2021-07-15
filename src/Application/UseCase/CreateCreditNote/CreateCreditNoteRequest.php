<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateCreditNote;

use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Ozean12\Money\Symfony\Validator\TaxedMoney as TaxedMoneyAssert;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(schema="CreateCreditNoteRequest", title="Create Credit Note",
 *     required={"external_code", "amount"},
 *     properties={
 *          @OA\Property(property="external_code", nullable=false, ref="#/components/schemas/TinyText", description="The code which this invoice will be identified by."),
 *          @OA\Property(property="amount", nullable=false, ref="#/components/schemas/AmountDTO", description="The amount that you want to create this credit note with."),
 *          @OA\Property(property="comment", ref="#/components/schemas/TinyText", nullable=true, description="Comment or description what is this credit note for."),
 *          @OA\Property(
 *              property="line_items",
 *              type="array",
 *              nullable=true,
 *              @OA\Items(ref="#/components/schemas/LineItemsRequest"),
 *              description="Line items from the invoice which this credit note is created for."
 *          )
 *     }
 * )
 */
class CreateCreditNoteRequest implements ValidatedRequestInterface
{
    private int $merchantId;

    /**
     * @Assert\Uuid()
     */
    private string $invoiceUuid;

    /**
     * @Assert\NotBlank()
     * @Assert\Valid()
     * @TaxedMoneyAssert\ValidTaxSum()
     * @var TaxedMoney
     */
    private $amount;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private $externalCode;

    /**
     * @Assert\Length(max=255)
     */
    private $externalComment;

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): self
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getInvoiceUuid(): string
    {
        return $this->invoiceUuid;
    }

    public function setInvoiceUuid($invoiceUuid): self
    {
        $this->invoiceUuid = $invoiceUuid;

        return $this;
    }

    public function getAmount(): TaxedMoney
    {
        return $this->amount;
    }

    public function setAmount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getExternalCode(): string
    {
        return $this->externalCode;
    }

    public function setExternalCode($externalCode): self
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getExternalComment(): ?string
    {
        return $this->externalComment;
    }

    public function setExternalComment($externalComment): self
    {
        $this->externalComment = $externalComment;

        return $this;
    }
}
