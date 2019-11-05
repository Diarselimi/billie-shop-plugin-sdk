<?php

namespace App\Support;

abstract class AbstractFactory
{
    abstract public function createFromArray(array $data);

    public function createFromArrayCollection(array $collection): array
    {
        return array_map([$this, 'createFromArray'], $collection);
    }
}
