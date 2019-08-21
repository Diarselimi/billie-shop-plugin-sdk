<?php

namespace App\Helper\Hasher;

use App\DomainModel\ArrayableInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ArrayHasher implements ArrayHasherInterface
{
    const ESCAPE_CHARACTERS = "!#@$%^&*() {}[]:;.,'?<>/\\\t\n\r\0\x0B";

    public function generateHash(ArrayableInterface $object, array $ignoreFields = []): string
    {
        $data = Arr::dot($object->toArray());
        $preparedArray = array_diff_key($data, array_flip($ignoreFields));

        $stringifiedData = trim(
            Str::lower(
                implode(' ', $preparedArray)
            ),
            self::ESCAPE_CHARACTERS
        );
        $stringifiedData = preg_replace('/\s+/', ' ', $stringifiedData);

        return md5($stringifiedData);
    }
}
