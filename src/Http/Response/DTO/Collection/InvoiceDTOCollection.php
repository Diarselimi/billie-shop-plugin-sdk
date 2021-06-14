<?php

declare(strict_types=1);

namespace App\Http\Response\DTO\Collection;

use App\Http\Response\DTO\InvoiceDTO;
use App\Support\CollectionInterface;

class InvoiceDTOCollection implements CollectionInterface
{
    /**
     * @var array
     */
    private array

 $elements;

    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }

    public function add(InvoiceDTO $invoice)
    {
        $this->elements[] = $invoice;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
    }

    public function count()
    {
        return count($this->elements);
    }

    public function toArray(): array
    {
        return array_map(fn (InvoiceDTO $invoice) => $invoice->toArray(), $this->elements);
    }
}
