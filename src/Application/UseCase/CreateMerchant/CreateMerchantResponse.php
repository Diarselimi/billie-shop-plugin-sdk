<?php

namespace App\Application\UseCase\CreateMerchant;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\Merchant\MerchantEntity;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="CreateMerchantResponse", allOf={@OA\Schema(ref="#/components/schemas/MerchantEntity")},
 *  x={"groups": {"private"}}, type="object", properties={
 *      @OA\Property(property="oauth_client_id", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="oauth_client_secret", ref="#/components/schemas/UUID"),
 * })
 */
class CreateMerchantResponse implements ArrayableInterface
{
    private $merchant;

    private $oauthClientId;

    private $oauthClientSecret;

    public function __construct(MerchantEntity $merchant, string $oauthClientId, string $oauthClientSecret)
    {
        $this->merchant = $merchant;
        $this->oauthClientId = $oauthClientId;
        $this->oauthClientSecret = $oauthClientSecret;
    }

    public function getMerchant(): MerchantEntity
    {
        return $this->merchant;
    }

    public function getOauthClientId(): string
    {
        return $this->oauthClientId;
    }

    public function getOauthClientSecret(): string
    {
        return $this->oauthClientSecret;
    }

    public function toArray(): array
    {
        return array_merge(
            $this->merchant->toArray(),
            ['oauth_client_id' => $this->oauthClientId, 'oauth_client_secret' => $this->oauthClientSecret]
        );
    }
}
