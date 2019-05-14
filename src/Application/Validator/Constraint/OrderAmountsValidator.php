<?php

namespace App\Application\Validator\Constraint;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OrderAmountsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $request = $this->context->getRoot();

        if ($request instanceof CreateOrderRequest) {
            $amountObj = $request->getAmount();

            if (!$amountObj->getGross() || !$amountObj->getNet() || !$amountObj->getTax()) {
                return;
            }

            if (!$this->checkAmounts($amountObj->getGross(), $amountObj->getNet(), $amountObj->getTax())) {
                $this->context->addViolation($constraint->message);
            }
        }

        if ($request instanceof UpdateOrderRequest) {
            if (!$request->getAmountGross() || !$request->getAmountNet() || !$request->getAmountTax()) {
                return;
            }

            if (!$this->checkAmounts($request->getAmountGross(), $request->getAmountNet(), $request->getAmountTax())) {
                $this->context->addViolation($constraint->message);
            }
        }
    }

    private function checkAmounts(float $gross, float $net, float $tax): bool
    {
        return bcadd($gross, 0, 5) === bcadd($net, $tax, 5);
    }
}
