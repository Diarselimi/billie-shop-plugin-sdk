<?php

namespace App\Helper\Uuid;

use Ramsey\Uuid\UuidInterface;

interface UuidFactoryInterface
{
    public function fromString(string $uuidString): UuidInterface;
}
