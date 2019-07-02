<?php

namespace App\Application\UseCase\ApiSpecLoad;

class ApiSpecNotFoundException extends \Exception
{
    public function __construct(string $specVariantName)
    {
        parent::__construct("API Specification file not found for group '{$specVariantName}'.");
    }
}
