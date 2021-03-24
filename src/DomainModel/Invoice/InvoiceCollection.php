<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice;

use App\DomainModel\CollectionInterface;
use Ozean12\Money\Money;

class InvoiceCollection implements CollectionInterface
{
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
}
