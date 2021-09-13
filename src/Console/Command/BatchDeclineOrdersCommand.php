<?php

namespace App\Console\Command;

use App\Application\UseCase\DeclineOrder\DeclineOrderRequest;
use App\Application\UseCase\DeclineOrder\DeclineOrderUseCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BatchDeclineOrdersCommand extends Command
{
    private const ARGUMENT_IDS = 'ids';

    protected static $defaultName = 'paella:order:decline-batch';

    private DeclineOrderUseCase $useCase;

    public function __construct(DeclineOrderUseCase $useCase)
    {
        parent::__construct();

        $this->useCase = $useCase;
    }

    protected function configure()
    {
        $this
            ->setDescription('Decline waiting/pre-waiting/authorized orders')
            ->addArgument(self::ARGUMENT_IDS, InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach (explode(',', $input->getArgument(self::ARGUMENT_IDS)) as $id) {
            $this->decline($id);
            $output->writeln("Order {$id} shipped");
        }

        return 0;
    }

    private function decline(string $id): void
    {
        $request = (new DeclineOrderRequest($id));
        $this->useCase->execute($request);
    }
}
