<?php

use App\Infrastructure\Phinx\TransactionalMigration;

class PopulateMerchantCompanyUuid extends TransactionalMigration
{
    public function migrate()
    {
        $paellaDbName = getenv('DATABASE_NAME');
        $dbNameSuffix = explode('paella', $paellaDbName)[1];
        $webappDbName = 'webapp' . $dbNameSuffix;

        try {
            $this->fetchAll("SELECT 1 from `$webappDbName`.companies LIMIT 1");
        } catch (PDOException $exception) {
            $this->getOutput()->writeln("<info>Webapp companies table doesn't exist, skipping data migration...</info>");

            return;
        }

        $this->execute("
            UPDATE `$paellaDbName`.merchants m
            INNER JOIN `$webappDbName`.companies c ON c.id = m.company_id
            SET m.company_uuid = c.uuid;
        ");
    }
}
