<?php

namespace App\Application\UseCase\ResendMerchantUserInvitation;

use App\Application\UseCase\CreateMerchantUserInvitation\CreateMerchantUserInvitationResponse;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntityFactory;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationNotFoundException;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationRepositoryInterface;

class ResendMerchantUserInvitationUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantUserRoleRepository;

    private $invitationRepository;

    private $invitationFactory;

    public function __construct(
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        MerchantUserInvitationRepositoryInterface $invitationRepository,
        MerchantUserInvitationEntityFactory $invitationFactory
    ) {
        $this->merchantUserRoleRepository = $merchantUserRoleRepository;
        $this->invitationRepository = $invitationRepository;
        $this->invitationFactory = $invitationFactory;
    }

    public function execute(ResendMerchantUserInvitationRequest $request): CreateMerchantUserInvitationResponse
    {
        $this->validateRequest($request);
        $invitation = $this->invitationRepository->findNonRevokedByUuidAndMerchant($request->getUuid(), $request->getMerchantId());

        if (!$invitation) {
            throw new MerchantUserInvitationNotFoundException();
        }

        if (!$invitation->isExpired()) {
            throw new ResendNotAllowedException();
        }

        $this->invitationRepository->revokeValidByEmailAndMerchant($invitation->getEmail(), $invitation->getMerchantId());

        $invitation = $this->invitationFactory->create(
            $invitation->getEmail(),
            $request->getMerchantId(),
            $invitation->getMerchantUserRoleId()
        );

        $this->invitationRepository->create($invitation);

        return new CreateMerchantUserInvitationResponse($invitation);
    }
}
