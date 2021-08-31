<?php

namespace App\Console\Command;

use App\Application\UseCase\ShipOrder\ShipOrderRequestV1;
use App\Application\UseCase\ShipOrder\ShipOrderUseCaseV1;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BatchShipOrdersCommand extends Command
{
    private const ARGUMENT_IDS = 'ids';

    private const MERCHANT_ID = 'merchant-id';

    protected static $defaultName = 'paella:order:ship-batch';

    private ShipOrderUseCaseV1 $shipUseCase;

    public function __construct(ShipOrderUseCaseV1 $shipUseCase)
    {
        parent::__construct();

        $this->shipUseCase = $shipUseCase;
    }

    protected function configure()
    {
        $this
            ->setDescription('Ship failed orders')
            ->addArgument(self::MERCHANT_ID, InputArgument::REQUIRED)
            ->addArgument(self::ARGUMENT_IDS, InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $merchantId = $input->getArgument(self::MERCHANT_ID);
        foreach (explode(',', $input->getArgument(self::ARGUMENT_IDS)) as $id) {
            $this->ship($id, $merchantId);
            $output->writeln("Order {$id} shipped");
        }

        return 0;
    }

    private function ship(string $id, string $merchantId): void
    {
        $request = (new ShipOrderRequestV1($id, $merchantId))
            ->setInvoiceNumber($id.'-1')
            ->setInvoiceUrl('Billie_Invoice_'.$id.'-1.pdf')
            ->setShippingDocumentUrl('')
        ;

        $this->shipUseCase->execute($request);
    }
}
