<?php

namespace App\DomainModel\MerchantUser;

class AuthenticationServiceConflictRequestException extends AuthenticationServiceRequestException
{
    protected $message = 'Conflicting resource creation in the %s service.';
}
