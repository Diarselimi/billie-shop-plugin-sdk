<?php

namespace App\Application\UseCase\MerchantUserLogin;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="MerchantUserLoginResponse", title="Merchant User Login Response", type="object", properties={
 *      @OA\Property(property="access_token", type="string", nullable=false, example="l387435hzyoc0oo4kokow"),
 *      @OA\Property(property="roles", type="array", @OA\Items(type="string", example=\App\DomainModel\MerchantUser\MerchantUserEntity::ROLE_USER))
 * })
 */
class MerchantUserLoginResponse implements ArrayableInterface
{
    private $accessToken;

    private $roles;

    public function __construct(string $accessToken, array $roles)
    {
        $this->accessToken = $accessToken;
        $this->roles = $roles;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function toArray(): array
    {
        return [
            'access_token' => $this->getAccessToken(),
            'roles' => $this->getRoles(),
        ];
    }
}
