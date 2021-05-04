<?php

namespace App\Application\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UpdateAmount extends Constraint
{
    public $message = 'The amount values are not correct.';
}
