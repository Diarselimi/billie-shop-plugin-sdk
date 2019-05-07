<?php

namespace App\Console\Command;

interface BatchCommandInterface
{
    public const BATCH_SIZE = 'batch';

    public const BATCH_SLEEP = 'sleep';
}
