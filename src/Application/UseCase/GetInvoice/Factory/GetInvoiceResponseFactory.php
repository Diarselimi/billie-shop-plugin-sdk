<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetInvoice\Factory;

use App\Application\UseCase\GetInvoice\GetInvoiceResponse;
use App\DomainModel\Invoice\CreditNote\CreditNote;
use App\DomainModel\Invoice\CreditNote\CreditNoteCollection;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\Http\Response\DTO\TaxedMoneyDTO;

class GetInvoiceResponseFactory
{
    public function create(Invoice $invoice, array $orderContainers): GetInvoiceResponse
    {
        return (new GetInvoiceResponse(
            $invoice,
            $this->createOrdersResponse($orderContainers),
            $this->createCreditNotesResponse($invoice->getCreditNotes())
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
                    'amount' => (new TaxedMoneyDTO($financialDetails->getAmountTaxedMoney()))->toArray(),
                ];
            },
            $orderContainers
        );
    }

    private function createCreditNotesResponse(CreditNoteCollection $creditNotes): array
    {
        return array_map(fn (CreditNote $creditNote) => $creditNote->toArray(), array_values($creditNotes->toArray()));
    }
}
