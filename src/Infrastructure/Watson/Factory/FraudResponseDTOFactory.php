<?php

declare(strict_types=1);

namespace App\Infrastructure\Watson\Factory;

use App\DomainModel\Fraud\FraudResponseDTO;

final class FraudResponseDTOFactory
{
    private const JSON_KEY_IS_FRAUD = 'is_fraud';

    public function createFromJson(array $json): FraudResponseDTO
    {
        $isFraud = $json[self::JSON_KEY_IS_FRAUD];

        return new FraudResponseDTO($isFraud);
    }
}
