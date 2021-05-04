<?php

namespace App\Console\Command;

use App\DomainModel\SynchronizeInvoices\LogOrdersService;
use App\DomainModel\SynchronizeInvoices\Connection;
use App\DomainModel\SynchronizeInvoices\SynchronizeInvoicesService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @see src/DomainModel/SynchronizeInvoices/README.md
 */
class SynchronizeInvoicesCommand extends Command
{
    private const LIMIT = 'limit';

    private const START_ID = 'start-id';

    private const DB_SUFFIX = 'db-suffix';

    protected static $defaultName = 'paella:sync-invoices';

    private SynchronizeInvoicesService $synchronizeService;

    private LogOrdersService $analyzeService;

    private Connection $connection;

    public function __construct(
        SynchronizeInvoicesService $synchronizeService,
        LogOrdersService $analyzeService,
        Connection $connection
    ) {
        $this->synchronizeService = $synchronizeService;
        $this->analyzeService = $analyzeService;
        $this->connection = $connection;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Synchronizes the invoices between the paella and invoice butler. Please refer to the src/DomainModel/SynchronizeInvoices/README.md for details')
            ->addOption(self::LIMIT, null, InputOption::VALUE_REQUIRED, 'Limit, e.g. number of orders to process', 1000)
            ->addOption(self::START_ID, null, InputOption::VALUE_REQUIRED, 'Start ID, e.g. first order id to process', 1)
            ->addOption(self::DB_SUFFIX, null, InputOption::VALUE_OPTIONAL, 'DB suffix, e.g. _super-possum', '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->connection->setDbSuffix($input->getOption(self::DB_SUFFIX));
        $this->analyzeService->shippedAndUnshipped($input->getOption(self::START_ID), $input->getOption(self::LIMIT));
        $this->synchronizeService->synchronize($input->getOption(self::START_ID), $input->getOption(self::LIMIT));
    }
}
