<?php

namespace App\DomainModel\Sandbox;

use App\DomainModel\AbstractServiceRequestException;

class SandboxServiceRequestException extends AbstractServiceRequestException
{
    public function getServiceName(): string
    {
        return 'paella-sandbox';
    }
}
