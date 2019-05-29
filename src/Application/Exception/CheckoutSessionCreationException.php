<?php

namespace App\Application\Exception;

class CheckoutSessionCreationException extends \RuntimeException
{
    protected $message = 'The checkout session creation failed';
}
