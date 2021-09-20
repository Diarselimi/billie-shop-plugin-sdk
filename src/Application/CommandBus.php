<?php

namespace App\Application;

interface CommandBus
{
    public function process(object $command): void;
}
