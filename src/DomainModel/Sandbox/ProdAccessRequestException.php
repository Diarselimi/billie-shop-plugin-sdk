<?php

namespace App\DomainModel\Sandbox;

use App\DomainModel\AbstractServiceRequestException;

class ProdAccessRequestException extends AbstractServiceRequestException
{
    public function getServiceName(): string
    {
        return 'paella-production';
    }
}
