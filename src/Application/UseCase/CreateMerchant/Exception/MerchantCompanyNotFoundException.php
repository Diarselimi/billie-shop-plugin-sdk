<?php

namespace App\Application\UseCase\CreateMerchant\Exception;

class MerchantCompanyNotFoundException extends \RuntimeException
{
    protected $message = "Merchant company with the given ID was not found or couldn't be retrieved";
}
