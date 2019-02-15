<?php

namespace App\Application\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class OrderExternalCode extends Constraint
{
    public $message = 'Order with code {{ value }} already exists';
}
