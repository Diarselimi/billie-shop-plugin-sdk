<?php

use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserPermissions;
use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantUserRepository;
use App\Infrastructure\Repository\MerchantUserRoleRepository;

class MigrateRoleIdToMerchantUsers extends TransactionalMigration
{
    protected function migrate()
    {
        $users = $this->fetchAll('SELECT * FROM ' . MerchantUserRepository::TABLE_NAME);

        foreach ($users as $user) {
            $permissions = $this->convertPermissions($user);
            $roleId = $this->deduceRoleId($user, $permissions);
            $permissionsJson = json_encode($permissions);
            $this->execute(
                "UPDATE " . MerchantUserRepository::TABLE_NAME .
                " SET role_id={$roleId}, permissions='{$permissionsJson}' WHERE id={$user['id']}"
            );
        }

        // assure that no new users or roles are created or changed by locking/unlocking these two tables
        $this->execute(
            'LOCK TABLES ' . MerchantUserRepository::TABLE_NAME . ' WRITE, '
            . MerchantUserRoleRepository::TABLE_NAME . ' WRITE'
        );
        $this
            ->table(MerchantUserRepository::TABLE_NAME)
            ->changeColumn('role_id', 'integer', ['null' => false, 'after' => 'last_name'])
            ->addForeignKey('role_id', 'merchant_user_roles', 'id')
            ->update();
        $this->execute('UNLOCK TABLES');
    }

    private function convertPermissions(array $user): array
    {
        $permissions = array_unique((array) json_decode($user['permissions']));
        sort($permissions);

        foreach ($permissions as $i => $permission) {
            $permissions[$i] = str_replace('ROLE_', '', $permission);
        }

        return $permissions;
    }

    private function deduceRoleName(array $permissions): string
    {
        if (empty($permissions)) {
            return MerchantUserDefaultRoles::ROLE_NONE['name'];
        }

        // consider it an admin if it has some write permission
        foreach (MerchantUserPermissions::ALL_WRITE_PERMISSIONS as $permissionName) {
            if (in_array($permissionName, $permissions ?: [], true)) {
                return MerchantUserDefaultRoles::ROLE_SUPPORT['name'];
            }
        }

        return MerchantUserDefaultRoles::ROLE_VIEW_ONLY['name'];
    }

    private function deduceRoleId(array $user, array $userPermissions): int
    {
        $roleName = $this->deduceRoleName($userPermissions);

        $role = $this->fetchRow("SELECT * FROM merchant_user_roles WHERE merchant_id={$user['merchant_id']} AND LOWER(name)='{$roleName}'");

        if (!$role || !$role['id']) {
            throw new \Exception("Role Not Found: {$roleName}");
        }

        return (int) $role['id'];
    }
}
