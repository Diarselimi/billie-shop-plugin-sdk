<?php

namespace App\Infrastructure\Monitoring\Sentry;

class SentryPrimaryKeySerializer implements \Raven_ObjectSerializerInterface
{
    public function supports($value)
    {
        return method_exists($value, 'getId');
    }

    public function serialize($value)
    {
        return sprintf("self::getId() [%s]", $value->getId());
    }
}
