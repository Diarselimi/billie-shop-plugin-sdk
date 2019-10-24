<?php

namespace App\Application\UseCase\RegisterMerchantUser;

class MerchantUserAlreadyExistsException extends \RuntimeException
{
    protected $message = "Merchant user with the same login already exists";
}
