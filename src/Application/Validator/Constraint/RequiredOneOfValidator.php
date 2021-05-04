<?php

namespace App\Application\Validator\Constraint;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RequiredOneOfValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof RequiredOneOf) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\OneIsRequired');
        }

        $request = $this->context->getRoot();
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($constraint->fields as $property) {
            if (!$propertyAccessor->isReadable($request, $property)) {
                throw new \InvalidArgumentException(sprintf('The method %s does not exist.', $property));
            }
            if ($propertyAccessor->getValue($request, $property) !== null) {
                return;
            }
        }

        $this->context->addViolation($constraint->message, ['{fields}' => implode(', ', $constraint->fields)]);
    }
}
