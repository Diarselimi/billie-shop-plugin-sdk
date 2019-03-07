<?php

namespace App\Application\Validator\Constraint;

use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\RegexValidator;

/**
 * @Annotation
 */
class Number extends Regex
{
    public $pattern = '/^[0-9]+(\.[0-9]{1,2})?$/';

    public function getRequiredOptions()
    {
        return [];
    }

    public function validatedBy()
    {
        return RegexValidator::class;
    }
}
