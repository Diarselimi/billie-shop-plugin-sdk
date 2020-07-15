<?php

declare(strict_types=1);

namespace App\DomainModel\CheckoutSession;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class CheckoutOrderMatcherViolationList extends ConstraintViolationList
{
    public function __construct(array $mismatches = [])
    {
        foreach ($mismatches as $property => $value) {
            $this->addMismatch($property, $value);
        }
    }

    public function hasMismatches(): bool
    {
        return $this->count() > 0;
    }

    public function addMismatch($property, $value): CheckoutOrderMatcherViolationList
    {
        if (!is_scalar($value) && !is_array($value)) {
            throw new \LogicException("Value should be scalar or array");
        }

        $message = "Value of [{$property}] does not match the original one.";
        $violation = new ConstraintViolation($message, $message, [], $property, $property, $value);
        $this->add($violation);

        return $this;
    }
}
