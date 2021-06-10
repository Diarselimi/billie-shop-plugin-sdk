<?php

namespace App\Application\UseCase\ExtendInvoice;

use Symfony\Component\Validator\Constraints as Assert;

class ExtendInvoiceRequest
{
    /**
     * @Assert\Uuid()
     * @Assert\NotBlank()
     * @var string
     */
    private string $invoiceUuid;

    /**
     * @Assert\Type(type="integer")
     * @Assert\GreaterThan(0)
     * @Assert\LessThanOrEqual(120)
     * @var int|null
     */
    private int $duration;

    private int $merchantId;

    public function __construct(string $invoiceUuid, int $duration, int $merchantId)
    {
        $this->invoiceUuid = $invoiceUuid;
        $this->duration = $duration;
        $this->merchantId = $merchantId;
    }

    public function getInvoiceUuid(): string
    {
        return $this->invoiceUuid;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }
}
