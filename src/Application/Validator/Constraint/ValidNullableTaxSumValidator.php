<?php

declare(strict_types=1);

namespace App\Application\Validator\Constraint;

use App\Support\NullableTaxedMoney;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidNullableTaxSumValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ($value === null) {
            return;
        }

        if (!$value instanceof NullableTaxedMoney) {
            throw new UnexpectedTypeException($value, NullableTaxedMoney::class);
        }

        if (!$constraint instanceof ValidNullableTaxSum) {
            throw new UnexpectedTypeException($constraint, ValidNullableTaxSum::class);
        }

        $netTaxSum = $value->getNet()->add($value->getTax());

        if (!$value->getGross()->equals($netTaxSum)) {
            $this->context
                ->buildViolation($constraint->message)
                ->setCode(ValidNullableTaxSum::ERROR_CODE)
                ->addViolation();
        }
    }
}
