<?php

namespace App\Application\Exception;

class OrderNotAuthorizedException extends \Exception
{
    protected $message = "The order is not authorized";
}
