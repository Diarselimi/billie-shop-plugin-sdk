<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice\CreditNote;

class CreditNoteNotAllowedException extends \RuntimeException
{
    protected $message = 'Credit note cannot be created for this invoice';
}
