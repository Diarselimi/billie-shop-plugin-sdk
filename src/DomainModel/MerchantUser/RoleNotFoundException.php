<?php

namespace App\DomainModel\MerchantUser;

class RoleNotFoundException extends \RuntimeException
{
    protected $message = 'Role not found';
}
