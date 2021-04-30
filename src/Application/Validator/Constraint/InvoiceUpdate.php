<?php

namespace App\Application\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class InvoiceUpdate extends Constraint
{
    public $message = 'One of the fields must be provided in order to update the invoice.';
}
