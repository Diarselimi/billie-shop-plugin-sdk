<?php

namespace App\Helper\Uuid;

use Ramsey\Uuid\UuidInterface;

/**
 * @deprecated use App\Infrastructure\UuidGeneration\UuidGenerator
 */
interface UuidGeneratorInterface
{
    public function uuid4(): string;

    public function uuid(): UuidInterface;
}
