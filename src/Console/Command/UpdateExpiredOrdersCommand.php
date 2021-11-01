<?php

declare(strict_types=1);

namespace App\Console\Command;

use App\Application\CommandBus;
use App\Application\UseCase\OrderExpiration\UpdateStateForExpiredOrdersCommand;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateExpiredOrdersCommand extends Command implements LoggingInterface
{
    use LoggingTrait;

    protected static $defaultName = 'paella:order:update-expired';

    private CommandBus $bus;

    public function __construct(
        CommandBus $bus
    ) {
        $this->bus = $bus;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Update orders state based on the expiration date.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = new UpdateStateForExpiredOrdersCommand(new \DateTime());

        $this->bus->process($command);

        return 0;
    }
}
