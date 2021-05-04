<?php

namespace App\DomainModel\OrderUpdate;

class UpdateOrderAmountException extends \RuntimeException
{
    public $message = 'Order amounts are not valid.';
}
