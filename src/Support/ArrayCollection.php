<?php

declare(strict_types=1);

namespace App\Support;

class ArrayCollection implements CollectionInterface
{
    protected array $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }
}
