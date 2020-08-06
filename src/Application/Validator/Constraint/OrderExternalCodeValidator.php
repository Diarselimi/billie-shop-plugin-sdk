<?php

namespace App\Application\Validator\Constraint;

use App\DomainModel\Order\OrderRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class OrderExternalCodeValidator extends ConstraintValidator
{
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof OrderExternalCode) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\ExternalCode');
        }

        $request = $this->context->getRoot();
        if (!$value || !$request->getMerchantId()) {
            return;
        }

        $order = $this->orderRepository->getOneByExternalCodeAndMerchantId($value, $request->getMerchantId());
        if (!$order) {
            return;
        }

        $this->context->addViolation($constraint->message, ['{{ value }}' => $value]);
    }
}
