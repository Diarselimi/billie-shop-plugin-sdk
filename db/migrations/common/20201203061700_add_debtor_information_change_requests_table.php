<?php

use App\Infrastructure\Phinx\MigrationHelperTrait;
use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\DebtorInformationChangeRequestRepository;
use App\Infrastructure\Repository\DebtorInformationChangeRequestTransitionRepository;

class AddDebtorInformationChangeRequestsTable extends TransactionalMigration
{
    use MigrationHelperTrait;

    public function migrate()
    {
        $this->table(DebtorInformationChangeRequestRepository::TABLE_NAME)
            ->addColumn('uuid', 'char', ['null' => false, 'limit' => 36])
            ->addColumn('company_uuid', 'char', ['null' => false, 'limit' => 36])
            ->addColumn('name', 'string', ['null' => false])
            ->addColumn('city', 'string', ['null' => false])
            ->addColumn('postal_code', 'string', ['null' => false])
            ->addColumn('street', 'string', ['null' => false])
            ->addColumn('house_number', 'string', ['null' => true])
            ->addColumn('merchant_user_id', 'integer', ['null' => false])
            ->addColumn('is_seen', 'boolean', ['null' => false])
            ->addColumn('state', 'string', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addIndex('company_uuid')
            ->addIndex('uuid', ['unique' => true])
            ->addForeignKey('merchant_user_id', 'merchant_users', 'id')
            ->create()
        ;

        $this->setupStateTransitionTableCreation(
            $this->table(DebtorInformationChangeRequestTransitionRepository::TABLE_NAME),
            'debtor_information_change_request_id',
            DebtorInformationChangeRequestRepository::TABLE_NAME
        )->create();
    }
}
