<?php

use App\Infrastructure\Repository\DebtorExternalDataRepository;
use Phinx\Migration\AbstractMigration;

class ChangeIndustrySectorToNullable extends AbstractMigration
{
    public function change()
    {
        $this
            ->table(DebtorExternalDataRepository::TABLE_NAME)
            ->changeColumn('industry_sector', 'string', ['null' => true])
            ->save()
        ;
    }
}
