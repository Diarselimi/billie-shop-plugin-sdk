<?php

namespace App\Application\UseCase\CreateMerchant\Exception;

class DuplicateMerchantCompanyException extends \RuntimeException
{
    protected $message = 'Merchant with the same company ID already exists';
}
