<?php

namespace App\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateHttpInvoicesCommand extends Command implements BatchCommandInterface
{
    use BatchCommandTrait;

    protected static $defaultName = 'paella:migrate-http-invoices';

    protected function configure()
    {
        $this->setDescription('Migrates all the existing http invoices from merchant to AWS');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \Exception('This command is disabled.');
    }
}
