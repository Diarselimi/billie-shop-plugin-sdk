<?php

use App\Infrastructure\Phinx\TransactionalMigration;

class MigrateDebtorWhitelistedFlag extends TransactionalMigration
{
    public function migrate()
    {
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        $sql = <<<SQL
INSERT INTO debtor_settings (company_uuid, is_whitelisted, created_at, updated_at)
SELECT company_uuid,
    is_whitelisted,
   '{$now}' as created_at,
   '{$now}' as updated_at
FROM merchants_debtors
GROUP BY company_uuid
ORDER BY is_whitelisted desc, created_at;
SQL;

        $this->execute($sql);
    }
}
