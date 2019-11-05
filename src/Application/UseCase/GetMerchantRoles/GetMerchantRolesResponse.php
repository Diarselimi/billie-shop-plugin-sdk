<?php

namespace App\Application\UseCase\GetMerchantRoles;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\MerchantUser\MerchantUserRoleEntity;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="GetMerchantRolesResponse",
 *      title="GetMerchantRolesResponse",
 *      x={"groups": {"dashboard"}},
 *      type="array",
 *      @OA\Items(
 *          type="object",
 *          properties={
 *              @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *              @OA\Property(property="name", ref="#/components/schemas/TinyText"),
 *          }
 *      )
 * )
 */
class GetMerchantRolesResponse implements ArrayableInterface
{
    /**
     * @var MerchantUserRoleEntity[]
     */
    private $roles;

    public function __construct(MerchantUserRoleEntity ...$roles)
    {
        $this->roles = $roles;
    }

    public function toArray(): array
    {
        $roles = [];

        foreach ($this->roles as $role) {
            $roles[] = [
                'uuid' => $role->getUuid(),
                'name' => $role->getName(),
            ];
        }

        return $roles;
    }
}
