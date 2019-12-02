<?php

namespace App\Application\Validator\Constraint;

use App\Application\UseCase\SignatoryPowersSelection\SignatoryPowersSelectionRequest;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SignatoryPowersIdentifiedUserValidator extends ConstraintValidator
{
    private const TOTAL_SELECTED_USERS = 1;

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof SignatoryPowersIdentifiedUser) {
            throw new UnexpectedTypeException($constraint, SignatoryPowersIdentifiedUser::class);
        }

        /** @var SignatoryPowersSelectionRequest $request */
        $request = $this->context->getRoot();

        if (!$value) {
            return;
        }

        $totalUsersSelectedAsIdentifiedUser = 0;
        foreach ($request->getSignatoryPowers() as $signatoryPowerDTO) {
            $totalUsersSelectedAsIdentifiedUser += (int) $signatoryPowerDTO->isIdentifiedAsUser();
        }

        if ($totalUsersSelectedAsIdentifiedUser > self::TOTAL_SELECTED_USERS) {
            $this->context->addViolation($constraint->message);
        }
    }
}
