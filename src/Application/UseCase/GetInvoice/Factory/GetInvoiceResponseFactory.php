<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetInvoice\Factory;

use App\Application\UseCase\GetInvoice\GetInvoiceResponse;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderContainer\OrderContainer;

class GetInvoiceResponseFactory
{
    public function create(Invoice $invoice, array $orderContainers): GetInvoiceResponse
    {
        return (new GetInvoiceResponse(
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
            $this->createOrdersResponse($orderContainers)
        ))->setUuid($invoice->getUuid());
    }

    /**
     * @param  OrderContainer[] $orderContainers
     * @return array[]
     */
    private function createOrdersResponse(array $orderContainers): array
    {
        return array_map(
            function (OrderContainer $orderContainer) {
                $financialDetails = $orderContainer->getOrderFinancialDetails();

                return [
                    'uuid' => $orderContainer->getOrder()->getUuid(),
                    'external_code' => $orderContainer->getOrder()->getExternalCode(),
                    'amount' => $financialDetails->getAmountGross()->getMoneyValue(),
                    'amount_net' => $financialDetails->getAmountNet()->getMoneyValue(),
                    'amount_tax' => $financialDetails->getAmountTax()->getMoneyValue(),
                ];
            },
            $orderContainers
        );
    }
}
