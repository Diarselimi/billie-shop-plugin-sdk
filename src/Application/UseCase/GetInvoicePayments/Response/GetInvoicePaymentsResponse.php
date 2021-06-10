<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetInvoicePayments\Response;

use App\DomainModel\Payment\BankTransaction;
use App\Support\CollectionInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="GetInvoicePaymentsResponse", title="Invoice Payments Response", type="object", properties={
 *      @OA\Property(
 *          property="summary", description="Invoice Payments Summary",
 *          ref="#/components/schemas/InvoicePaymentSummaryDTO"
 *      ),
 *      @OA\Property(
 *          property="items", type="array", description="Bank Transaction object",
 *          @OA\Items(ref="#/components/schemas/BankTransactionDTO")
 *      ),
 *      @OA\Property(property="total", type="integer", description="Total number of items"),
 * })
 */
final class GetInvoicePaymentsResponse implements CollectionInterface
{
    private InvoicePaymentSummary $summary;

    private array $items;

    public function __construct()
    {
        $this->summary = new InvoicePaymentSummary();
        $this->items = [];
    }

    public function getSummary(): InvoicePaymentSummary
    {
        return $this->summary;
    }

    public function setSummary(InvoicePaymentSummary $summary): GetInvoicePaymentsResponse
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return BankTransaction[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(BankTransaction $bankTransaction): GetInvoicePaymentsResponse
    {
        $this->items[] = $bankTransaction;

        return $this;
    }

    public function getTotal(): int
    {
        return count($this->items);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return $this->getTotal();
    }

    public function toArray(): array
    {
        return [
            'summary' => $this->getSummary()->toArray(),
            'items' => array_map(static fn (BankTransaction $tx) => $tx->toArray(), $this->getItems()),
            'total' => $this->getTotal(),
        ];
    }
}
