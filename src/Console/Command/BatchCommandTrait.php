<?php

namespace App\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

trait BatchCommandTrait
{
    protected $currentIteration;

    protected $size;

    protected $sleep;

    protected function configureBatch()
    {
        $this
            ->addOption(self::BATCH_SIZE, null, InputOption::VALUE_REQUIRED, 'Batch size', 50)
            ->addOption(self::BATCH_SLEEP, null, InputOption::VALUE_REQUIRED, 'Sleep time between batches, in seconds', 5)
        ;
    }

    protected function iteration(InputInterface $input)
    {
        if ($this->currentIteration === null) {
            $this->currentIteration = 1;
            $this->size = (int) $input->getOption(self::BATCH_SIZE);
            $this->sleep = (int) $input->getOption(self::BATCH_SLEEP);

            return;
        }

        if ($this->currentIteration === $this->size) {
            sleep($this->sleep);
            $this->currentIteration = 1;

            return;
        }

        $this->currentIteration++;
    }
}
