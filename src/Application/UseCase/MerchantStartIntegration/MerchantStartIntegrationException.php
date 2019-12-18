<?php

namespace App\Application\UseCase\MerchantStartIntegration;

class MerchantStartIntegrationException extends \RuntimeException
{
    protected $message = 'Merchant integration cannot be started at this point.';
}
