<?php

namespace App\DomainModel\Payment;

use App\DomainModel\AbstractServiceRequestException;

class PaymentsServiceRequestException extends AbstractServiceRequestException
{
    public function getServiceName(): string
    {
        return 'payments';
    }
}
