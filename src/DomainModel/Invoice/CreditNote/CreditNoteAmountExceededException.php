<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice\CreditNote;

class CreditNoteAmountExceededException extends \Exception
{
    protected $message = 'The credit note amount cannot be higher than the invoice outstanding amount.';
}
