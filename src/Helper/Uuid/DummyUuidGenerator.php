<?php

namespace App\Helper\Uuid;

use App\Helper\Uuid\UuidGeneratorInterface;

/**
 * This class is used for Behat tests and acts as a uuid mocker which returns always static valid uuid
 */
class DummyUuidGenerator implements UuidGeneratorInterface
{
    const DUMMY_UUID4 = '6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4';

    public function uuid4(): string
    {
        return self::DUMMY_UUID4;
    }
}
