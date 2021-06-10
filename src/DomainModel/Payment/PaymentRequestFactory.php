<?php

namespace App\DomainModel\Payment;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Payment\RequestDTO\ConfirmRequestDTO;
use Ozean12\Money\Money;

class PaymentRequestFactory
{
    public function createConfirmRequestDTO(OrderEntity $order, float $paidAmount): ConfirmRequestDTO
    {
        $requestDTO = new ConfirmRequestDTO($paidAmount);
        $requestDTO
            ->setPaymentUuid($order->getPaymentId())
            ->setInvoiceNumber($order->getInvoiceNumber())
            ->setExternalCode($order->getExternalCode())
            ->setShippedAt($order->getShippedAt());

        return $requestDTO;
    }

    public function createConfirmRequestDTOFromInvoice(
        Invoice $invoice,
        Money $paidAmount,
        string $orderExternalCode
    ): ConfirmRequestDTO {
        $requestDTO = new ConfirmRequestDTO($paidAmount->getMoneyValue());
        $requestDTO
            ->setPaymentUuid($invoice->getPaymentUuid())
            ->setInvoiceNumber($invoice->getExternalCode())
            ->setExternalCode($orderExternalCode)
            ->setShippedAt($invoice->getCreatedAt());

        return $requestDTO;
    }
}
