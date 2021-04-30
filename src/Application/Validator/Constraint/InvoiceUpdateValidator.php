<?php

namespace App\Application\Validator\Constraint;

use App\Application\UseCase\UpdateInvoice\UpdateInvoiceRequest;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class InvoiceUpdateValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof InvoiceUpdate) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\InvoiceUpdate');
        }

        /** @var UpdateInvoiceRequest $request */
        $request = $this->context->getRoot();
        if ($request->getExternalCode() === null && $request->getInvoiceUrl() === null) {
            $this->context->addViolation($constraint->message, ['{{ value }}' => $value]);
        }
    }
}
