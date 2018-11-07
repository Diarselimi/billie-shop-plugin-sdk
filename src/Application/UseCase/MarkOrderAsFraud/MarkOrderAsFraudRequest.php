<?php

namespace App\Application\UseCase\MarkOrderAsFraud;

class MarkOrderAsFraudRequest
{
    private $uuid;

    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
