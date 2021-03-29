<?php

namespace App\DomainModel\Payment\RequestDTO;

use App\DomainModel\Payment\AbstractPaymentRequestDTO;

class ModifyRequestDTO extends AbstractPaymentRequestDTO
{
    public function toArray(): array
    {
        return [
            'ticket_id' => $this->getPaymentUuid(),
            'invoice_number' => $this->getInvoiceNumber(),
            'duration' => $this->getDuration(),
            'amount' => $this->getAmountGross(),
        ];
    }
}
