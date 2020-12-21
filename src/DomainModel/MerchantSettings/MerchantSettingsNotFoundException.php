<?php

declare(strict_types=1);

namespace App\DomainModel\MerchantSettings;

class MerchantSettingsNotFoundException extends \RuntimeException
{
    protected $message = 'Merchant settings are missing';
}
