<?php

namespace App\Application\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class InvoiceExternalCode extends Constraint
{
    public $message = 'Invoice with code {{ value }} already exists';
}
