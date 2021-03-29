<?php

namespace App\DomainModel\Payment\RequestDTO;

use App\DomainModel\Payment\AbstractPaymentRequestDTO;

class ConfirmRequestDTO extends AbstractPaymentRequestDTO
{
    private $amount;

    public function __construct(float $amount)
    {
        $this->amount = $amount;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): ConfirmRequestDTO
    {
        $this->amount = $amount;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'ticket_id' => $this->getPaymentUuid(),
            'amount' => $this->getAmount(),
        ];
    }
}
