<?php

use App\Infrastructure\Repository\PublicDomainEmailRepository;
use Phinx\Migration\AbstractMigration;

class CreatePublicEmailTableAndInsertDomains extends AbstractMigration
{
    public function change()
    {
        $this
            ->table(PublicDomainEmailRepository::TABLE_NAME)
            ->addColumn('domain', 'string', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addIndex('domain')
            ->save()
        ;

        $this->populateTable();
    }

    private function populateTable()
    {
        $datetime = (new DateTime())->format('Y-m-d H:i:s');
        $sql = "INSERT INTO ".PublicDomainEmailRepository::TABLE_NAME." (`domain`, `created_at`) VALUES ('%s', '%s')";
        $lines = file(__DIR__ . '/../../../src/Resources/email_provider_domains.txt');
        foreach ($lines as $line) {
            $this->execute(
                sprintf($sql, htmlspecialchars(trim($line)), $datetime)
            );
        }
    }
}
