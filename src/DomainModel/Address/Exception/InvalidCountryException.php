<?php

declare(strict_types=1);

namespace App\DomainModel\Address\Exception;

class InvalidCountryException extends InvalidAddressException
{
    protected $message = 'Country is not in a valid format.';
}
