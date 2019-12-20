<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantUserRepository;
use App\Support\RandomStringGenerator;
use Ramsey\Uuid\Uuid;

class AddMerchantUserInvitationsTable extends TransactionalMigration
{
    protected function migrate()
    {
        $tableName = 'merchant_user_invitations';
        $this
            ->table($tableName)
            ->addColumn('uuid', 'string', ['null' => false, 'limit' => 36])
            ->addColumn('token', 'string', ['null' => false, 'limit' => 36])
            ->addColumn('merchant_id', 'integer', ['null' => false])
            ->addColumn('merchant_user_id', 'integer', ['null' => true, 'default' => null])
            ->addColumn('merchant_user_role_id', 'integer', ['null' => false])
            ->addColumn('email', 'string', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('expires_at', 'datetime', ['null' => false])
            ->addColumn('revoked_at', 'datetime', ['null' => true, 'default' => null])
            ->addForeignKey('merchant_id', 'merchants', 'id')
            ->addForeignKey('merchant_user_id', 'merchant_users', 'id')
            ->addForeignKey('merchant_user_role_id', 'merchant_user_roles', 'id')
            ->addIndex(['uuid'], ['unique' => true])
            ->addIndex(['token'], ['unique' => true])
            ->create();

        $users = $this->fetchAll('SELECT * FROM ' . MerchantUserRepository::TABLE_NAME);
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $table = $this->table($tableName);

        $tokenGenerator = new RandomStringGenerator();

        foreach ($users as $user) {
            $table->insert([
                'uuid' => Uuid::uuid4(),
                'token' => $tokenGenerator->generateHexToken(),
                'merchant_id' => $user['merchant_id'],
                'merchant_user_id' => $user['id'],
                'merchant_user_role_id' => $user['role_id'],
                'email' => 'migrated@billie.io',
                'created_at' => $user['created_at'],
                'expires_at' => $now,
            ])->save();
        }
    }
}
