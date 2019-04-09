<?php

namespace App\Application\Validator\Constraint;

use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\RangeValidator;

/**
 * @Annotation
 */
class OrderAmountConstraint extends Range
{
    public $min = 1;

    public $max = 120;

    public function validatedBy()
    {
        return RangeValidator::class;
    }
}
