<?php

namespace App\Infrastructure\Repository;

use ArrayIterator;
use IteratorAggregate;

class SearchResultIterator implements IteratorAggregate
{
    /**
     * @var int
     */
    private $total;

    /**
     * @var array
     */
    private $items;

    public function __construct(int $total, array $items)
    {
        $this->total = $total;
        $this->items = $items;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
}
