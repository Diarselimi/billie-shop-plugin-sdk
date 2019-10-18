<?php

namespace App\DomainModel\MerchantUser;

class MerchantUserPermissionsService
{
    private $merchantUserRoleRepository;

    public function __construct(MerchantUserRoleRepositoryInterface $merchantUserRoleRepository)
    {
        $this->merchantUserRoleRepository = $merchantUserRoleRepository;
    }

    /**
     * Returns the user role with the resolved permissions
     *
     * @param  MerchantUserEntity     $user
     * @return MerchantUserRoleEntity
     */
    public function resolveUserRole(MerchantUserEntity $user): MerchantUserRoleEntity
    {
        $role = $this->merchantUserRoleRepository->getOneById($user->getRoleId());

        if (!$role) {
            throw new RoleNotFoundException();
        }

        if (!empty($user->getPermissions())) {
            $role->setPermissions($user->getPermissions());

            return $role;
        }

        return $role;
    }
}
