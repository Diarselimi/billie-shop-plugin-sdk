<?php

namespace App\Application\UseCase\RevokeMerchantUserInvitation;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationNotFoundException;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationRepositoryInterface;

class RevokeMerchantUserInvitationUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $invitationRepository;

    public function __construct(
        MerchantUserInvitationRepositoryInterface $invitationRepository
    ) {
        $this->invitationRepository = $invitationRepository;
    }

    public function execute(RevokeMerchantUserInvitationRequest $request): void
    {
        $this->validateRequest($request);
        $invitation = $this->invitationRepository->findNonRevokedByUuidAndMerchant($request->getUuid(), $request->getMerchantId());

        if (!$invitation || $invitation->isExpired() || $invitation->getMerchantUserId() !== null) {
            throw new MerchantUserInvitationNotFoundException();
        }

        $this->invitationRepository->revokeValidByEmailAndMerchant($invitation->getEmail(), $invitation->getMerchantId());
    }
}
