<?php

namespace App\Infrastructure\UuidGeneration;

use Ramsey\Uuid\Uuid;

class RamseyUuidGenerator implements UuidGenerator
{
    public function generate(): string
    {
        return Uuid::uuid4()->toString();
    }
}
