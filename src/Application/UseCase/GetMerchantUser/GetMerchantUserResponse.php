<?php

namespace App\Application\UseCase\GetMerchantUser;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="GetMerchantUserResponse", title="Get Merchant User Response", type="object", properties={
 *      @OA\Property(property="user_id", type="integer", nullable=false),
 *      @OA\Property(
 *          property="permissions",
 *          type="array",
 *          nullable=false,
 *          @OA\Items(ref="#/components/schemas/MerchantUserRoles")
 *      )
 * })
 */
class GetMerchantUserResponse implements ArrayableInterface
{
    private $userId;

    private $roles;

    public function __construct(int $userId, array $roles)
    {
        $this->userId = $userId;
        $this->roles = $roles;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->getUserId(),
            'permissions' => $this->getRoles(),
        ];
    }
}
