<?php

namespace App\Infrastructure\UuidGeneration;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class RamseyUuidGenerator implements UuidGenerator
{
    public function generate(): string
    {
        return Uuid::uuid4()->toString();
    }

    public function uuid(): UuidInterface
    {
        return Uuid::uuid4();
    }
}
