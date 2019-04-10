<?php

namespace App\Helper\Hasher;

use App\DomainModel\ArrayableInterface;

class ArrayHasher implements ArrayHasherInterface
{
    public function generateHash(ArrayableInterface $object, array $ignoreFields = []): string
    {
        $preparedArray = array_diff_key($object->toArray(), array_flip($ignoreFields));

        $stringifiedData = preg_replace(
            '/[^A-Za-z0-9äöüßé]/',
            '',
            strtolower(implode("", $preparedArray))
        );

        return md5($stringifiedData);
    }
}
