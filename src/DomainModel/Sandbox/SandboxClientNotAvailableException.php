<?php

namespace App\DomainModel\Sandbox;

class SandboxClientNotAvailableException extends \RuntimeException
{
    protected $message = 'Sandbox Client is not available';
}
