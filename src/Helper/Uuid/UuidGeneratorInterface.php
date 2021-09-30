<?php

namespace App\Helper\Uuid;

use Ramsey\Uuid\UuidInterface;

interface UuidGeneratorInterface
{
    public function uuid4(): string;

    public function uuid(): UuidInterface;
}
