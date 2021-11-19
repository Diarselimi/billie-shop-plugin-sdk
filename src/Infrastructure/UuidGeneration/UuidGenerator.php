<?php

namespace App\Infrastructure\UuidGeneration;

use Ramsey\Uuid\UuidInterface;

interface UuidGenerator
{
    public function generate(): string;

    public function uuid(): UuidInterface;
}
