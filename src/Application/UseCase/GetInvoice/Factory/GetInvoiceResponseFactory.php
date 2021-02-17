<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetInvoice\Factory;

use App\Application\UseCase\GetInvoice\GetInvoiceResponse;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderEntity;

class GetInvoiceResponseFactory
{
    public function create(Invoice $invoice, array $orders): GetInvoiceResponse
    {
        return new GetInvoiceResponse(
            $invoice->getExternalCode(),
            $invoice->getDuration(),
            $invoice->getPayoutAmount()->toFloat(),
            $invoice->getAmount()->getGross()->toFloat(),
            $invoice->getAmount()->getNet()->toFloat(),
            $invoice->getAmount()->getTax()->toFloat(),
            $invoice->getOutstandingAmount()->toFloat(),
            $invoice->getMerchantPendingPaymentAmount()->toFloat(),
            $invoice->getInvoicePendingCancellationAmount()->toFloat(),
            $invoice->getFeeAmount()->getGross()->toFloat(),
            $invoice->getFeeRate()->toFloat(),
            $invoice->getCreatedAt(),
            $invoice->getDueDate(),
            $invoice->getState(),
            $this->createOrdersResponse($orders)
        );
    }

    private function createOrdersResponse(array $orders): array
    {
        return array_map(function (OrderEntity $order) {
            return [
                'uuid' => $order->getUuid(),
                'external_code' => $order->getExternalCode(),
            ];
        }, $orders);
    }
}
