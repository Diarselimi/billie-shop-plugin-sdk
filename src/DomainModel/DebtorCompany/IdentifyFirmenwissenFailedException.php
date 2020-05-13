<?php

namespace App\DomainModel\DebtorCompany;

class IdentifyFirmenwissenFailedException extends \RuntimeException
{
    protected $message = 'Firmenwissen identification failed';
}
