<?php

namespace App\Application\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class RequiredOneOf extends Constraint
{
    public string $message = 'One of the fields is required [{fields}]';

    public array $fields = [];

    public function getDefaultOption(): array
    {
        return [];
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return get_class($this).'Validator';
    }
}
