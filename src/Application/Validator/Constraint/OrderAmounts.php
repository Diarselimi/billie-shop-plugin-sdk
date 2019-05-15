<?php

namespace App\Application\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class OrderAmounts extends Constraint
{
    public $message = 'Invalid amounts';
}
