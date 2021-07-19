<?php

namespace App\Helper\Uuid;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidFactory implements UuidFactoryInterface
{
    public function fromString(string $uuidString): UuidInterface
    {
        return Uuid::fromString($uuidString);
    }
}
