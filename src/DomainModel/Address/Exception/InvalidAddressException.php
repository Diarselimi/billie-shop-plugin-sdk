<?php

declare(strict_types=1);

namespace App\DomainModel\Address\Exception;

class InvalidAddressException extends \RuntimeException
{
    protected $message = 'The Address data are not valid.';
}
