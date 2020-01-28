<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetSignatoryPowerDetails;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\SignatoryPower\SignatoryPowerDTO;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="GetSignatoryPowerDetailsResponse",
 *      type="object",
 *      properties={
 *          @OA\Property(property="merchant_name", type="string", example="Example GmbH"),
 *          @OA\Property(property="identity_verification_url", type="string", format="url"),
 *      }
 * )
 */
class GetSignatoryPowerDetailsResponse implements ArrayableInterface
{
    private $merchantName;

    private $signatoryPowerDTO;

    public function __construct(string $merchantName, SignatoryPowerDTO $signatoryPowerDTO)
    {
        $this->signatoryPowerDTO = $signatoryPowerDTO;
        $this->merchantName = $merchantName;
    }

    public function toArray(): array
    {
        return [
            'merchant_name' => $this->merchantName,
            'identity_verification_url' => $this->signatoryPowerDTO->getIdentityVerificationUrl(),
        ];
    }
}
