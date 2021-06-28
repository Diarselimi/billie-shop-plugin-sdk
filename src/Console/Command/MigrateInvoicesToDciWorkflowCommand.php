<?php

namespace App\Console\Command;

use Billie\PdoBundle\Infrastructure\Pdo\PdoConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/*
 * One time command to migration Boost invoices to invoice_dci_workflows table
 */
class MigrateInvoicesToDciWorkflowCommand extends Command
{
    protected static $defaultName = 'paella:migrate-invoices-dci';

    private $connection;

    public function __construct(PdoConnection $db)
    {
        $this->connection = $db;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Migrate Boost invoices to invoice_dci_workflows table')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sql = 'INSERT IGNORE INTO webapp.`invoice_dci_workflows` (`invoice_id`, `uuid`, `state`, `dci_enabled`, `created_at`, `updated_at`)
SELECT i.id, UUID(), CASE
  WHEN po.state in ("shipped", "paid_out") THEN "created"
  WHEN po.state = "complete" THEN "complete"
  WHEN po.state = "late" THEN "late"
  WHEN po.state = "canceled" THEN "canceled"
  END AS "dci_state", 1, po.shipped_at, po.shipped_at
FROM webapp.invoices i
JOIN paella.order_invoices_v2 poi ON poi.invoice_uuid = i.uuid
JOIN paella.orders po ON po.id = poi.order_id;';

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
    }
}
