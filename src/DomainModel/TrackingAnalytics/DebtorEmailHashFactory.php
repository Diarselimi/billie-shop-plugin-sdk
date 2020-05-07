<?php

namespace App\DomainModel\TrackingAnalytics;

use Ramsey\Uuid\Uuid;

final class DebtorEmailHashFactory
{
    private const UUID_NS = '24cd9f61-3592-46b4-bf2d-67a68e05f313';

    public static function create(?string $email): ?string
    {
        return $email !== null
            ? Uuid::uuid5(self::UUID_NS, $email)->toString()
            : null
        ;
    }
}
