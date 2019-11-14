<?php

namespace App\DomainModel\MerchantUser;

class MerchantUserAlreadyExistsException extends \RuntimeException
{
    protected $message = "Merchant user with the same login already exists";
}
