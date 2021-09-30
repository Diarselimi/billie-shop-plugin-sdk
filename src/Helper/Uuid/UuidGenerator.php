<?php

namespace App\Helper\Uuid;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidGenerator implements UuidGeneratorInterface
{
    /**
     * @deprecated
     * @see \Ozean12\Support\Random\RandomStringGenerator::uuid4()
     */
    public function uuid4(): string
    {
        return Uuid::uuid4()->toString();
    }

    public function uuid(): UuidInterface
    {
        return Uuid::uuid4();
    }
}
