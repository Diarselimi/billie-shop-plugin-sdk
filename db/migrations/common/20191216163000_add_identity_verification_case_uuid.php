<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantUserRepository;

class AddIdentityVerificationCaseUuid extends TransactionalMigration
{
    protected function migrate()
    {
        $this->table(MerchantUserRepository::TABLE_NAME)
            ->addColumn('identity_verification_case_uuid', 'string', [
                'null' => true,
                'after' => 'role_id',
                'limit' => 36,
            ])
            ->save();
    }
}
