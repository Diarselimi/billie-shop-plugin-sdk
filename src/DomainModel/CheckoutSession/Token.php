<?php

namespace App\DomainModel\CheckoutSession;

class Token
{
    private const LENGTH = 36;

    private string $hash;

    private function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    public static function fromSeed(string $seed): self
    {
        $hash = substr(hash('sha256', $seed), 0, self::LENGTH);
        $hash = substr($hash, 0, self::LENGTH);

        return new self($hash);
    }

    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    public function __toString(): string
    {
        return $this->hash;
    }
}
