<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice\CreditNote;

use App\DomainModel\CollectionInterface;
use Ozean12\Money\Money;

class CreditNoteCollection implements CollectionInterface
{
    /**
     * @var CreditNote[]
     */
    private array $elements;

    public function __construct(array $elements)
    {
        $this->elements = [];
        foreach ($elements as $creditNote) {
            if ($creditNote instanceof CreditNote) {
                $this->elements[$creditNote->getUuid()] = $creditNote;
            }
        }
    }

    public function add(CreditNote $creditNote): CreditNoteCollection
    {
        $this->elements[$creditNote->getUuid()] = $creditNote;

        return $this;
    }

    public function getGrossSum(): Money
    {
        $sum = new Money(0);
        foreach ($this->elements as $creditNote) {
            $sum = $sum->add($creditNote->getAmount()->getGross());
        }

        return $sum;
    }

    public function getNetSum(): Money
    {
        $sum = new Money(0);
        foreach ($this->elements as $creditNote) {
            $sum = $sum->add($creditNote->getAmount()->getNet());
        }

        return $sum;
    }

    public function getTaxSum(): Money
    {
        $sum = new Money(0);
        foreach ($this->elements as $creditNote) {
            $sum = $sum->add($creditNote->getAmount()->getTax());
        }

        return $sum;
    }

    public function toArray(): array
    {
        return $this->elements;
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function clear(): void
    {
        $this->elements = [];
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->elements);
    }

    public function pop(): ?CreditNote
    {
        return array_pop($this->elements);
    }
}
