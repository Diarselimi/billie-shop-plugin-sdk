<?php

namespace App\DomainModel\Merchant;

class DuplicateMerchantCompanyException extends \RuntimeException
{
    protected $message = 'Merchant with the same company ID already exists';
}
