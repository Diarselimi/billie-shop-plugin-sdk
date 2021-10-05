<?php

use Phinx\Migration\AbstractMigration;

class AddLineItemsRiskCheck extends AbstractMigration
{
    public function change()
    {
        $table = 'risk_check_rules';
        $this
            ->table($table)
            ->addColumn('excluded_words', 'json', ['null' => false])
            ->addColumn('included_words', 'json', ['null' => false])
            ->addColumn('check_email_public_domain', 'boolean', ['default' => true, 'null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->save();

        $included = json_encode(['Download', 'ESD']);
        $excluded = json_encode(['Lizenz']);

        $sql = <<<SQL
INSERT INTO {$table} (excluded_words, included_words, check_email_public_domain, updated_at, created_at)
VALUES ('{$excluded}', '{$included}', 1, NOW(), NOW());
SQL;
        $this->execute($sql);
    }
}
