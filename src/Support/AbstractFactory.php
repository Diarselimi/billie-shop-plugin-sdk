<?php

namespace App\Support;

abstract class AbstractFactory
{
    abstract public function createFromArray(array $data);

    public function createFromArrayMultiple(iterable $arrays): array
    {
        $transformedCollection = [];

        foreach ($arrays as $k => $item) {
            $transformedCollection[$k] = $this->createFromArray($item);
        }

        return $transformedCollection;
    }
}
