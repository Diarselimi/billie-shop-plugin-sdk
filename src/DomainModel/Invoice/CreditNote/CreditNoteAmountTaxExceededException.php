<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice\CreditNote;

class CreditNoteAmountTaxExceededException extends \Exception
{
    protected $message = 'The credit note amount tax cannot be higher than the amount or the original invoice tax.';
}
