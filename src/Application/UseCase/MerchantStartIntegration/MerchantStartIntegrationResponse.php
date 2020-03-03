<?php

namespace App\Application\UseCase\MerchantStartIntegration;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="MerchantStartIntegrationResponse",
 *      title="MerchantStartIntegrationResponse",
 *      x={"groups": {"private"}},
 *      type="object",
 *      properties={
 *          @OA\Property(property="production", type="object", nullable=true,
 *              properties={
 *                  @OA\Property(property="client_id", type="string"),
 *                  @OA\Property(property="client_secret", type="string"),
 *              }
 *          ),
 *          @OA\Property(property="sandbox", type="object", nullable=false,
 *              properties={
 *                  @OA\Property(property="client_id", type="string"),
 *                  @OA\Property(property="client_secret", type="string"),
 *              }
 *          ),
 *      }
 * )
 */
class MerchantStartIntegrationResponse implements ArrayableInterface
{
    private $sandboxClientId;

    private $sandboxClientSecret;

    public function __construct(string $sandboxClientId, string $sandboxClientSecret)
    {
        $this->sandboxClientId = $sandboxClientId;
        $this->sandboxClientSecret = $sandboxClientSecret;
    }

    public function toArray(): array
    {
        return [
            'production_credentials' => null,
            'sandbox_credentials' => [
                'client_id' => $this->sandboxClientId,
                'client_secret' => $this->sandboxClientSecret,
            ],
        ];
    }
}
