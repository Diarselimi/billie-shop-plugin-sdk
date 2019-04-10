<?php

namespace App\Helper\Hasher;

use App\DomainModel\ArrayableInterface;

interface ArrayHasherInterface
{
    public function generateHash(ArrayableInterface $object, array $ignoreFields = []): string;
}
