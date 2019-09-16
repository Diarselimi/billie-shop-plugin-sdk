<?php

namespace App\Support;

use App\DomainModel\ArrayableInterface;
use ArrayIterator;
use Countable;
use IteratorAggregate;

class PaginatedCollection implements IteratorAggregate, Countable, ArrayableInterface
{
    private $items;

    private $total;

    public function __construct(array $items = [], int $total = 0)
    {
        $this->items = $items;
        $this->total = $total;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->getItems());
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function map(callable $fn): PaginatedCollection
    {
        $this->items = array_map($fn, $this->items);

        return $this;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function count()
    {
        return count($this->items);
    }

    public function toArray(): array
    {
        return [
            'items' => $this->getItems(),
            'total' => $this->getTotal(),
        ];
    }
}
