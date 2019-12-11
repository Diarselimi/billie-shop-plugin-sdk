<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetMerchantCredentials;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 *
 * @OA\Schema(schema="GetMerchantCredentialsResponse",
 *     type="object",
 *     properties={
 *          @OA\Property(property="production_credentials", type="object", nullable=true,
 *              properties={
 *                  @OA\Property(property="client_id", type="string", example="6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4"),
 *                  @OA\Property(property="client_secret", type="string", example="sGTOI43UNLSKlkfdji4jfscn2812"),
 *              }),
 *          @OA\Property(property="sandbox_credentials", type="object", nullable=true, properties={
 *                  @OA\Property(property="client_id", type="string", example="6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4"),
 *                  @OA\Property(property="client_secret", type="string", example="sGTOI43UNLSKlkfdji4jfscn2812"),
 *              }),
 *      })
 */
class GetMerchantCredentialsResponse implements ArrayableInterface
{
    private $production = null;

    private $sandbox = null;

    public function setProduction(string $clientId, string $secret): GetMerchantCredentialsResponse
    {
        $this->production = [
            'client_id' => $clientId,
            'client_secret' => $secret,
        ];

        return $this;
    }

    public function setSandbox(string $clientId, string $secret): GetMerchantCredentialsResponse
    {
        $this->sandbox = [
            'client_id' => $clientId,
            'client_secret' => $secret,
        ];

        return $this;
    }

    public function toArray(): array
    {
        return [
            'production_credentials' => $this->production,
            'sandbox_credentials' => $this->sandbox,
        ];
    }
}
