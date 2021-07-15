<?php

namespace App\Application\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UpdateAmount extends Constraint
{
    public $message = 'The new order amount should be less then existing order amount';
}
