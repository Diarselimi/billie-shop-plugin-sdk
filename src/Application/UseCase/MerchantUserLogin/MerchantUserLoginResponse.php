<?php

namespace App\Application\UseCase\MerchantUserLogin;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="MerchantUserLoginResponse", title="Merchant User Login Response", type="object", properties={
 *      @OA\Property(property="user_id", type="integer", nullable=false),
 *      @OA\Property(property="access_token", type="string", nullable=false, example="l387435hzyoc0oo4kokow"),
 *      @OA\Property(
 *          property="roles",
 *          type="array",
 *          nullable=false,
 *          @OA\Items(type="string", example=\App\DomainModel\MerchantUser\MerchantUserEntity::ROLE_USER)
 *      ),
 *      @OA\Property(property="merchant_name", type="string", nullable=false, example="Billie GmbH")
 * })
 */
class MerchantUserLoginResponse implements ArrayableInterface
{
    private $userId;

    private $accessToken;

    private $roles;

    private $merchantName;

    public function __construct(int $userId, string $accessToken, array $roles, string $merchantName)
    {
        $this->userId = $userId;
        $this->accessToken = $accessToken;
        $this->roles = $roles;
        $this->merchantName = $merchantName;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getMerchantName(): string
    {
        return $this->merchantName;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->getUserId(),
            'access_token' => $this->getAccessToken(),
            'roles' => $this->getRoles(),
            'merchant_name' => $this->getMerchantName(),
        ];
    }
}
