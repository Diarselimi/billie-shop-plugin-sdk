<?php

declare(strict_types=1);

namespace App\DomainModel\OrderFinancialDetails;

class OrderFinancialDetailsNotFoundException extends \RuntimeException
{
    protected $message = 'Order financial details not found.';
}
