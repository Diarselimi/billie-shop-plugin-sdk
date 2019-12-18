<?php

namespace App\Application\UseCase\CreateMerchantWithCompany;

class CompanyCreationException extends \RuntimeException
{
    protected $message = 'Merchant company creation failed.';
}
