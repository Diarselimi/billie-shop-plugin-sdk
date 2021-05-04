<?php

namespace App\Application\Validator\Constraint;

use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UpdateAmountValidator extends ConstraintValidator
{
    private OrderFinancialDetailsRepositoryInterface $financialDetailsRepository;

    public function __construct(
        OrderFinancialDetailsRepositoryInterface $financialDetailsRepository
    ) {
        $this->financialDetailsRepository = $financialDetailsRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UpdateAmount) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\UpdateAmount');
        }

        /** @var UpdateOrderRequest $request */
        $request = $this->context->getRoot();
        if (!$value || !$request->isAmountChanged()) {
            return;
        }

        $financialDetails = $this->financialDetailsRepository->findOneByOrderUuid($request->getOrderUuid());
        if ($financialDetails->getAmountGross()->lessThan($request->getAmount()->getGross()) ||
            $financialDetails->getAmountNet()->lessThan($request->getAmount()->getNet()) ||
            $financialDetails->getAmountTax()->lessThan($request->getAmount()->getTax())
        ) {
            $this->context->addViolation($constraint->message);
        }
    }
}
