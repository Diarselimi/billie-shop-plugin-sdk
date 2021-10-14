<?php

namespace App\Application\Exception;

class MerchantDebtorNotFoundException extends \RuntimeException
{
    protected $message = 'Merchant Debtor Not Found';
}
