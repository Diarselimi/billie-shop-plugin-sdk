<?php

namespace App\DomainModel\MerchantUser;

use App\DomainModel\AbstractServiceRequestException;

class AuthenticationServiceRequestException extends AbstractServiceRequestException
{
    public function getServiceName(): string
    {
        return 'authentication';
    }
}
