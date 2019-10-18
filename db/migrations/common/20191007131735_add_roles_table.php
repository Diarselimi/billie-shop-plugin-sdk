<?php

use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\Infrastructure\Phinx\TransactionalMigration;
use Ramsey\Uuid\Uuid;

class AddRolesTable extends TransactionalMigration
{
    protected function migrate()
    {
        $tableName = 'merchant_user_roles';
        $this
            ->table($tableName)
            ->addColumn('uuid', 'string', ['null' => false, 'limit' => 36])
            ->addColumn('merchant_id', 'integer', ['null' => false])
            ->addColumn('name', 'string', ['null' => false])
            ->addColumn('permissions', 'json', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('merchant_id', 'merchants', 'id')
            ->addIndex(['merchant_id', 'name'], ['unique' => true])
            ->addIndex(['uuid'], ['unique' => true])
            ->create();

        $merchants = $this->fetchAll('SELECT * FROM merchants');
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $table = $this->table($tableName);

        foreach ($merchants as $merchant) {
            foreach (MerchantUserDefaultRoles::ROLES as $role) {
                $table->insert([
                    'uuid' => Uuid::uuid4(),
                    'merchant_id' => $merchant['id'],
                    'name' => $role['name'],
                    'permissions' => json_encode($role['permissions']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->save();
            }
        }
    }
}
