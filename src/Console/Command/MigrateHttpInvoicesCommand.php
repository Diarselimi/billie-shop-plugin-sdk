<?php

namespace App\Console\Command;

use App\Application\UseCase\HttpInvoiceUpload\HttpInvoiceUploadRequest;
use App\Application\UseCase\HttpInvoiceUpload\HttpInvoiceUploadUseCase;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderInvoice\InvoiceUploadHandlerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateHttpInvoicesCommand extends Command implements BatchCommandInterface
{
    use BatchCommandTrait;

    protected static $defaultName = 'paella:migrate-http-invoices';

    private $useCase;

    private $orderRepository;

    public function __construct(
        HttpInvoiceUploadUseCase $useCase,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->useCase = $useCase;
        $this->orderRepository = $orderRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Migrates all the existing http invoices from merchant to AWS');
        $this->configureBatch();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->orderRepository->getOrdersByInvoiceHandlingStrategy(MerchantSettingsEntity::INVOICE_HANDLING_STRATEGY_HTTP) as $order) {
            $this->iteration($input);
            $output->write("<comment>{$order->getExternalCode()}</comment> ");

            try {
                $this->useCase->execute(new HttpInvoiceUploadRequest(
                    $order->getMerchantId(),
                    $order->getExternalCode(),
                    $order->getInvoiceUrl(),
                    $order->getInvoiceNumber(),
                    InvoiceUploadHandlerInterface::EVENT_MIGRATION
                ));

                $output->writeln("<info>OK</info>");
            } catch (\Exception $exception) {
                $output->writeln("<error>{$exception->getMessage()}</error>");
            }
        }

        return 0;
    }
}
