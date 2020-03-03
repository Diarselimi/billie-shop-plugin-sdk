<?php

namespace App\DomainModel\OrderPayments;

use App\Support\DateFormat;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="OrderPaymentDTO", type="object", properties={
 *      @OA\Property(property="amount", type="number", format="float"),
 *      @OA\Property(property="created_at", ref="#/components/schemas/DateTime"),
 *      @OA\Property(property="state", type="string"),
 *      @OA\Property(property="type", type="string")
 * })
 */
class OrderPaymentDTO
{
    public const PAYMENT_TYPE_INVOICE_PAYBACK = 'invoice_payback';

    public const PAYMENT_STATE_NEW = 'new';

    public const PAYMENT_STATE_COMPLETE = 'complete';

    private $createdAt;

    private $amount;

    private $type;

    private $state;

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): OrderPaymentDTO
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): OrderPaymentDTO
    {
        $this->amount = $amount;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): OrderPaymentDTO
    {
        $this->type = $type;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): OrderPaymentDTO
    {
        $this->state = $state;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->getAmount(),
            'created_at' => $this->getCreatedAt()->format(DateFormat::FORMAT_YMD_HIS),
            'state' => $this->getState(),
            'type' => $this->getType(),
        ];
    }
}
