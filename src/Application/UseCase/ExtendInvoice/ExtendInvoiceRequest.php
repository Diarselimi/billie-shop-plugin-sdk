<?php

namespace App\Application\UseCase\ExtendInvoice;

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

    public function __construct(string $invoiceUuid, int $duration)
    {
        $this->invoiceUuid = $invoiceUuid;
        $this->duration = $duration;
    }

    public function getInvoiceUuid(): string
    {
        return $this->invoiceUuid;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }
}
