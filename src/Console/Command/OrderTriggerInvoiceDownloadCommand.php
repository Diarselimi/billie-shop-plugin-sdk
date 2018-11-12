<?php

namespace App\Console\Command;

use App\Application\UseCase\OrderTriggerInvoiceDownload\OrderTriggerInvoiceDownloadUseCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OrderTriggerInvoiceDownloadCommand extends Command
{
    private $useCase;

    public function __construct(OrderTriggerInvoiceDownloadUseCase $useCase)
    {
        $this->useCase = $useCase;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('paella:order-trigger-invoice-download')
            ->setDescription('Emits a download event for all the orders having an invoice number.')
            ->setHelp(
                'This command goes through all the orders with an
            invoice_number and triggers an event to download and archive the related document.'
            )
            ->addOption(
                'limit',
                null,
                InputOption::VALUE_REQUIRED,
                'Limits the number of processed rows and events.',
                1000
            )
            ->addOption('last-id', null, InputOption::VALUE_REQUIRED, 'Last successfully processed order ID.', 0)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = (int) $input->getOption('limit');
        $lastId = (int) $input->getOption('last-id');
        $newLastId = $this->useCase->execute($limit, $lastId);

        if ($lastId === $newLastId) {
            $output->writeln("There are no more orders to process.");

            return;
        }

        $output->writeln("Last successfully processed order ID:  {$newLastId}");
    }
}
