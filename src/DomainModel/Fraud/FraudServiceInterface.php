<?php

declare(strict_types=1);

namespace App\DomainModel\Fraud;

interface FraudServiceInterface
{
    public function check(FraudRequestDTO $request): FraudResponseDTO;
}
