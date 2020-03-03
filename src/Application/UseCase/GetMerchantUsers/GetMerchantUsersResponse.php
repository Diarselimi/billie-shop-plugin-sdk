<?php

namespace App\Application\UseCase\GetMerchantUsers;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\MerchantUser\MerchantUserRoleEntity;
use App\DomainModel\MerchantUserInvitation\MerchantInvitedUserDTO;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="GetMerchantUsersResponse", title="Merchant Users List", type="object", properties={
 *     @OA\Property(property="total", type="integer", nullable=false),
 *     @OA\Property(property="items", nullable=false, type="array", @OA\Items(type="object", properties={
 *          @OA\Property(property="uuid", @OA\Schema(ref="#/components/schemas/UUID", nullable=true)),
 *          @OA\Property(property="first_name", type="string", nullable=true),
 *          @OA\Property(property="last_name", type="string", nullable=true),
 *          @OA\Property(property="email", type="string", format="email", nullable=false),
 *          @OA\Property(property="role", type="object", nullable=false, properties={
 *              @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *              @OA\Property(property="name", ref="#/components/schemas/TinyText", example="Support", description="Role internal name"),
 *          }),
 *          @OA\Property(property="invitation_uuid", ref="#/components/schemas/UUID", nullable=true),
 *          @OA\Property(
 *              property="invitation_status",
 *              type="string",
 *              enum=\App\DomainModel\MerchantUserInvitation\MerchantInvitedUserDTO::INVITATION_STATUSES,
 *              nullable=false
 *          )
 *     }))
 * })
 */
class GetMerchantUsersResponse implements ArrayableInterface
{
    private $totalCount;

    private $users;

    private $roles;

    /**
     * @param int                      $totalCount
     * @param MerchantInvitedUserDTO[] $users
     * @param MerchantUserRoleEntity[] $roles
     */
    public function __construct(int $totalCount, array $users, array $roles)
    {
        $this->totalCount = $totalCount;
        $this->users = $users;
        $this->roles = [];

        foreach ($roles as $role) {
            $this->roles[$role->getId()] = $role;
        }
    }

    public function getMerchantUsers(): array
    {
        return $this->users;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function toArray(): array
    {
        return [
            'total' => $this->getTotalCount(),
            'items' => array_map([$this, 'toArraySingle'], $this->getMerchantUsers()),
        ];
    }

    private function toArraySingle(MerchantInvitedUserDTO $user)
    {
        $role = $this->roles[$user->getRoleId()];

        return [
            'uuid' => $user->getUserId(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'email' => $user->getEmail(),
            'role' => [
                'uuid' => $role->getUuid(),
                'name' => $role->getName(),
            ],
            'invitation_uuid' => $user->getInvitationStatus() != MerchantInvitedUserDTO::INVITATION_STATUS_COMPLETE ?
                $user->getInvitationUuid() : null,
            'invitation_status' => $user->getInvitationStatus(),
        ];
    }
}
