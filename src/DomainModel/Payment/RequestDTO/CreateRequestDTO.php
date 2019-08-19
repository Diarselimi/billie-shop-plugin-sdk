<?php

namespace App\DomainModel\Payment\RequestDTO;

use App\DomainModel\Payment\AbstractPaymentRequestDTO;

class CreateRequestDTO extends AbstractPaymentRequestDTO
{
    public function toArray(): array
    {
        return [
            'debtor_id' => $this->getDebtorPaymentId(),
            'payment_id' => $this->getPaymentId(),
            'invoice_number' => $this->getInvoiceNumber(),
            'billing_date' => !is_null($this->getShippedAt()) ? $this->getShippedAt()->format('Y-m-d') : null,
            'duration' => $this->getDuration(),
            'amount' => $this->getAmountGross(),
            'order_code' => $this->getExternalCode(),
        ];
    }
}
