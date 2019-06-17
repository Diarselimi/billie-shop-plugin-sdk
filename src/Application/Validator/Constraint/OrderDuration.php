<?php

namespace App\Application\Validator\Constraint;

use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\RangeValidator;

/**
 * @Annotation
 */
class OrderDuration extends Range
{
    public const DURATION_MIN = 1;

    public const DURATION_MAX = 120;

    public $min = self::DURATION_MIN;

    public $max = self::DURATION_MAX;

    public function validatedBy()
    {
        return RangeValidator::class;
    }
}
