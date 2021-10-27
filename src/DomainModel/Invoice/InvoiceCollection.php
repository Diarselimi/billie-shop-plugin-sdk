<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice;

use App\Support\CollectionInterface;
use Ozean12\Money\Money;

class InvoiceCollection implements CollectionInterface
{
    /**
     * @return Invoice[]
     */
    private array

 $elements;

    public function __construct(array $elements)
    {
        $this->elements = [];
        foreach ($elements as $invoice) {
            if ($invoice instanceof Invoice) {
                $this->elements[$invoice->getUuid()] = $invoice;
            }
        }
    }

    public function get(string $uuid): ?Invoice
    {
        return $this->elements[$uuid] ?? null;
    }

    public function getInvoicesCreditNotesGrossSum(): Money
    {
        $totalAmount = new Money(0);

        /** @var Invoice $invoice */
        foreach ($this->elements as $invoice) {
            $totalAmount = $totalAmount->add($invoice->getCreditNotes()->getGrossSum());
        }

        return $totalAmount;
    }

    public function getInvoicesCreditNotesNetSum(): Money
    {
        $totalAmount = new Money(0);

        /** @var Invoice $invoice */
        foreach ($this->elements as $invoice) {
            $totalAmount = $totalAmount->add($invoice->getCreditNotes()->getNetSum());
        }

        return $totalAmount;
    }

    public function getLastInvoice(): ?Invoice
    {
        return end($this->elements) ? end($this->elements) : null;
    }

    /**
     * @return Invoice[]
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function isEmpty(): bool
    {
        return count($this->elements) <= 0;
    }

    public function clear(): void
    {
        $this->elements = [];
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->elements);
    }

    public function add(Invoice $invoice): InvoiceCollection
    {
        $this->elements[$invoice->getUuid()] = $invoice;

        return $this;
    }

    public function getFirst(): ?Invoice
    {
        return reset($this->elements) ? reset($this->elements) : null;
    }

    /**
     * @return Invoice[]
     */
    public function keyByUuid(): array
    {
        return $this->elements;
    }

    public function hasOpenInvoices(): bool
    {
        return count(array_filter(
            $this->elements,
            fn ($invoice) => in_array(
                $invoice->getState(),
                [Invoice::STATE_NEW, Invoice::STATE_LATE, Invoice::STATE_PAID_OUT]
            )
        )) > 0;
    }

    public function hasCompletedInvoice(): bool
    {
        return count(array_filter($this->elements, fn (Invoice $invoice) => $invoice->isComplete())) > 0;
    }

    public function hasPartiallyPaidInvoice(): bool
    {
        return count(array_filter($this->elements, static function (Invoice $invoice) {
            return $invoice->getOutstandingAmount()->lessThan($invoice->getGrossAmount())
                && !$invoice->isCanceled()
                && !$invoice->isComplete()
            ;
        })) > 0;
    }

    public function getUuids(): array
    {
        return array_map(fn (Invoice $i) => $i->getUuid(), $this->elements);
    }
}
