<?php

declare(strict_types=1);

namespace App\Application\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @inheritDoc
 */
class ValidNullableTaxSum extends Constraint
{
    public const ERROR_CODE = '791915bb-7704-4cd6-bd0a-1b84bc134f05';

    public $message = 'Invalid values: gross is not equal to net + tax.';

    public function getTargets()
    {
        return [
            self::CLASS_CONSTRAINT,
            self::PROPERTY_CONSTRAINT,
        ];
    }
}
