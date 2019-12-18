<?php

namespace App\Application\UseCase\MerchantStartIntegration;

class MerchantStartIntegrationNotAllowedException extends \RuntimeException
{
    protected $message = 'Merchant integration cannot be started at this point.';
}
