<?php

namespace App\DomainModel\MerchantUser;

class MerchantUserNotFoundException extends \RuntimeException
{
    protected $message = 'Merchant user not found';
}
