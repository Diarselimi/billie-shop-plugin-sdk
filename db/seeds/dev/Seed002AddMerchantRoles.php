<?php

use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\Infrastructure\Repository\MerchantRepository;
use App\Infrastructure\Repository\MerchantUserRoleRepository;
use Phinx\Seed\AbstractSeed;
use Ramsey\Uuid\Uuid;

class Seed002AddMerchantRoles extends AbstractSeed
{
    private const FIRST_ADMIN_ROLE_UUID = '4c3fbc94-79bc-48ca-be55-b93d6cd833bd';

    public function run()
    {
        $merchants = $this->fetchAll('SELECT * FROM ' . MerchantRepository::TABLE_NAME);
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $table = $this->table(MerchantUserRoleRepository::TABLE_NAME);

        $firstAdminUuid = self::FIRST_ADMIN_ROLE_UUID;
        foreach ($merchants as $merchant) {
            if ($this->merchantHasRoles($merchant['id'])) {
                continue;
            }
            foreach (MerchantUserDefaultRoles::ROLES as $role) {
                if ($firstAdminUuid && $role['name'] === 'admin') {
                    $uuid = $firstAdminUuid;
                    $firstAdminUuid = null;
                } else {
                    $uuid = Uuid::uuid4();
                }

                $table->insert(
                    [
                        'uuid' => $uuid,
                        'merchant_id' => $merchant['id'],
                        'name' => $role['name'],
                        'permissions' => json_encode($role['permissions']),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                )->save();
            }
        }
    }

    private function merchantHasRoles($merchantId): bool
    {
        $stmt = $this->query(
            "SELECT id FROM " . MerchantUserRoleRepository::TABLE_NAME . " WHERE merchant_id='{$merchantId}'"
        );

        return $stmt ? (bool) $stmt->fetch() : false;
    }
}
