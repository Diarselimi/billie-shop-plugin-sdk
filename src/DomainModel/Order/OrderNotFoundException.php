<?php

namespace App\DomainModel\Order;

class OrderNotFoundException extends \RuntimeException
{
    protected $message = "Order not found";
}
