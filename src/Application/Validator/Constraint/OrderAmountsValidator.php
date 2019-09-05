<?php

namespace App\Application\Validator\Constraint;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAmountRequest;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OrderAmountsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $amountRequest = $this->context->getObject();

        if (!$amountRequest instanceof CreateOrderAmountRequest) {
            return;
        }

        if (is_null($amountRequest->getGross()) || is_null($amountRequest->getNet()) || is_null($amountRequest->getTax())) {
            return;
        }

        if (!$this->checkAmounts($amountRequest->getGross(), $amountRequest->getNet(), $amountRequest->getTax())) {
            $this->context->addViolation($constraint->message);
        }
    }

    private function checkAmounts(float $gross, float $net, float $tax): bool
    {
        return bcadd($gross, 0, 5) === bcadd($net, $tax, 5);
    }
}
