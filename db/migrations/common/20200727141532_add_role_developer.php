<?php

use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantUserRoleRepository;
use Ramsey\Uuid\Uuid;

class AddRoleDeveloper extends TransactionalMigration
{
    protected function migrate()
    {
        $now = date('Y-m-d H:i:s');

        $rows = [];
        $merchants = $this->fetchAll('SELECT * FROM merchants');

        foreach ($merchants as $merchant) {
            $merchantId = $merchant['id'];

            $rows[] = [
                'uuid' => Uuid::uuid4(),
                'merchant_id' => $merchantId,
                'name' => MerchantUserDefaultRoles::ROLE_DEVELOPER['name'],
                'permissions' => json_encode(MerchantUserDefaultRoles::ROLE_DEVELOPER['permissions']),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->table(MerchantUserRoleRepository::TABLE_NAME)->insert($rows)->save();
    }
}
