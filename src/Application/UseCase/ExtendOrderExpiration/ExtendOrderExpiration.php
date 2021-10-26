<?php

namespace App\Application\UseCase\ExtendOrderExpiration;

class ExtendOrderExpiration
{
    private string $oderUuid;

    private \DateTimeImmutable $newExpiration;

    public function __construct(string $oderUuid, \DateTimeImmutable $newExpiration)
    {
        $this->oderUuid = $oderUuid;
        $this->newExpiration = $newExpiration;
    }

    public function oderUuid(): string
    {
        return $this->oderUuid;
    }

    public function newExpiration(): \DateTimeImmutable
    {
        return $this->newExpiration;
    }
}
