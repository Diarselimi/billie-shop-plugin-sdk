<?php

namespace App\Support;

abstract class AbstractFactory
{
    abstract public function createFromArray(array $data);

    public function createFromArrayCollection(iterable $collection): array
    {
        $transformedCollection = [];

        foreach ($collection as $k => $item) {
            $transformedCollection[$k] = $this->createFromArray($item);
        }

        return $transformedCollection;
    }
}
