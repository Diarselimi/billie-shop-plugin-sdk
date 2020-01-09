<?php

declare(strict_types=1);

namespace App\DomainModel\IdentityVerification;

class IdentityVerificationStartResponseDTO
{
    private $uuid;

    private $url;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): IdentityVerificationStartResponseDTO
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): IdentityVerificationStartResponseDTO
    {
        $this->url = $url;

        return $this;
    }
}
