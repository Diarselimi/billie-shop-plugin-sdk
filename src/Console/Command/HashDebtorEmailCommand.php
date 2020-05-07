<?php

namespace App\Console\Command;

use App\DomainModel\TrackingAnalytics\DebtorEmailHashFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HashDebtorEmailCommand extends Command
{
    private const NAME = 'paella:debtor:hash-email';

    const ARGUMENT_EMAIL = 'email';

    public function __construct()
    {
        parent::__construct(self::NAME);
    }

    protected function configure()
    {
        $this->addArgument(self::ARGUMENT_EMAIL, InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $hash = DebtorEmailHashFactory::create(
            $input->getArgument(self::ARGUMENT_EMAIL)
        );

        $output->writeln($hash);
    }
}
