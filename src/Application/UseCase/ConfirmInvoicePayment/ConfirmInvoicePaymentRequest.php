<?php

declare(strict_types=1);

namespace App\Application\UseCase\ConfirmInvoicePayment;

use App\Application\UseCase\ValidatedRequestInterface;
use Ozean12\Money\Money;
use Ozean12\Money\Symfony\Validator\Decimal as MoneyAssert;
use Symfony\Component\Validator\Constraints as Assert;

class ConfirmInvoicePaymentRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\Uuid()
     */
    private string $invoiceUuid;

    private int $merchantId;

    /**
     * @MoneyAssert\GreaterThan(value=0)
     * @MoneyAssert\IsNumeric()
     * @MoneyAssert\NotBlank()
     */
    private Money $paidAmount;

    public function __construct(string $invoiceUuid, int $merchantId, Money $paidAmount)
    {
        $this->invoiceUuid = $invoiceUuid;
        $this->merchantId = $merchantId;
        $this->paidAmount = $paidAmount;
    }

    public function getInvoiceUuid(): string
    {
        return $this->invoiceUuid;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getPaidAmount(): Money
    {
        return $this->paidAmount;
    }
}
