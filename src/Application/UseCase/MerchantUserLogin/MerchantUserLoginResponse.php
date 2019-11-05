<?php

namespace App\Application\UseCase\MerchantUserLogin;

use App\Application\UseCase\GetMerchantUser\GetMerchantUserResponse;
use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="MerchantUserLoginResponse", title="Merchant User Login Response", type="object", properties={
 *      @OA\Property(property="access_token", type="string", nullable=false, example="l387435hzyoc0oo4kokow", description="Bearer JWT Token"),
 *      @OA\Property(property="user", ref="#/components/schemas/GetMerchantUserResponse", nullable=false),
 * })
 */
class MerchantUserLoginResponse implements ArrayableInterface
{
    private $userResponse;

    private $accessToken;

    public function __construct(GetMerchantUserResponse $userResponse, string $accessToken)
    {
        $this->userResponse = $userResponse;
        $this->accessToken = $accessToken;
    }

    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'user' => $this->userResponse->toArray(),
        ];
    }
}
