<?php

namespace App\DomainModel\Sandbox;

class SandboxCreationException extends \RuntimeException
{
    protected $message = 'Sandbox merchant cannot be created at this point.';
}
