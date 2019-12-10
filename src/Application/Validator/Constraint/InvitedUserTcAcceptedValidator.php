<?php

namespace App\Application\Validator\Constraint;

use App\Application\UseCase\RegisterInvitedMerchantUser\RegisterInvitedMerchantUserRequest;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class InvitedUserTcAcceptedValidator extends ConstraintValidator
{
    private $roleRepository;

    public function __construct(MerchantUserRoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof InvitedUserTcAccepted) {
            throw new UnexpectedTypeException($constraint, InvitedUserTcAccepted::class);
        }

        /** @var RegisterInvitedMerchantUserRequest $request */
        $request = $this->context->getRoot();

        $invitation = $request->getInvitation();
        $role = $this->roleRepository->getOneById($invitation->getMerchantUserRoleId(), $invitation->getMerchantId());

        if (!$role->isTcAcceptanceRequired()) {
            return;
        }

        if (!$value) {
            $this->context->addViolation($constraint->message);
        }
    }
}
