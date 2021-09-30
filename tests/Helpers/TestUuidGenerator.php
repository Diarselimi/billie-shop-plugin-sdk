<?php

declare(strict_types=1);

namespace App\Tests\Helpers;

use App\Helper\Uuid\UuidGeneratorInterface;
use App\Tests\Functional\Context\PaellaCoreContext;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class TestUuidGenerator implements UuidGeneratorInterface
{
    private const DEFAULT_UUIDS = [
        PaellaCoreContext::DUMMY_UUID4,
        '8e6a9efa-3a76-44f1-ad98-24f0ef15d7ad',
        'b2a21065-a9a3-45fd-8236-9a6329f21f70',
        '7e2fd925-4356-4f6d-adc8-fc17dfcec6a7',
        '5060e904-6814-480d-8154-149677d0e8a5',
        '0919324f-fc36-4a79-9213-0c55aff2f56a',
        'e2e5ae68-c065-43f8-883d-b2589a5c0f56',
        'e4ab7f06-4f5e-4ac2-9998-4750c0e18f49',
    ];

    private static array

 $constantUuids = self::DEFAULT_UUIDS;

    private static bool $dynamic = false;

    public static function enableDynamicGeneration()
    {
        self::$dynamic = true;
    }

    public static function disableDynamicGeneration()
    {
        self::$dynamic = false;
    }

    public static function getUuids(): array
    {
        return self::$constantUuids;
    }

    public static function appendConstantUuid(string $uuid): void
    {
        self::$constantUuids[] = $uuid;
    }

    public static function resetConstantUuids()
    {
        self::$constantUuids = self::DEFAULT_UUIDS;
    }

    public function uuid(): UuidInterface
    {
        return Uuid::fromString(self::DEFAULT_UUIDS[2]);
    }

    public function uuid4(): string
    {
        if (self::$dynamic === true) {
            return Uuid::uuid4()->toString();
        }

        if (empty(self::$constantUuids)) {
            self::$constantUuids = self::DEFAULT_UUIDS;
        }

        return array_pop(self::$constantUuids);
    }
}
