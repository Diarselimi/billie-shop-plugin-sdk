<?php

declare(strict_types=1);

namespace App\DomainModel\IdentityVerification;

interface IdentityVerificationServiceInterface
{
    public function startVerificationCase(IdentityVerificationStartRequestDTO $requestDTO): IdentityVerificationStartResponseDTO;
}
