<?php

namespace App\Helper\Uuid;

use Ramsey\Uuid\Uuid;

class UuidGenerator implements UuidGeneratorInterface
{
    public function uuid4(): string
    {
        return Uuid::uuid4()->toString();
    }
}
