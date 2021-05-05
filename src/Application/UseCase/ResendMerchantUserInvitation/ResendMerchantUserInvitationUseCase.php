<?php

namespace App\Application\UseCase\ResendMerchantUserInvitation;

use App\Application\UseCase\CreateMerchantUserInvitation\CreateMerchantUserInvitationResponse;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Merchant\MerchantNotFoundException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUser\RoleNotFoundException;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntity;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntityFactory;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationNotFoundException;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationRepositoryInterface;
use Ozean12\Transfer\Message\MerchantUserInvitation\MerchantUserInvitationCreated;
use Symfony\Component\Messenger\MessageBusInterface;

class ResendMerchantUserInvitationUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private MerchantUserRoleRepositoryInterface $merchantUserRoleRepository;

    private MerchantRepositoryInterface $merchantRepository;

    private MerchantUserInvitationRepositoryInterface $invitationRepository;

    private MerchantUserInvitationEntityFactory $invitationFactory;

    private MessageBusInterface $messageBus;

    public function __construct(
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        MerchantRepositoryInterface $merchantRepository,
        MerchantUserInvitationRepositoryInterface $invitationRepository,
        MerchantUserInvitationEntityFactory $invitationFactory,
        MessageBusInterface $messageBus
    ) {
        $this->merchantUserRoleRepository = $merchantUserRoleRepository;
        $this->merchantRepository = $merchantRepository;
        $this->invitationRepository = $invitationRepository;
        $this->invitationFactory = $invitationFactory;
        $this->messageBus = $messageBus;
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

        $this->publishMessage($invitation);

        return new CreateMerchantUserInvitationResponse($invitation);
    }

    private function publishMessage(MerchantUserInvitationEntity $invitation): void
    {
        $role = $this->merchantUserRoleRepository->getOneById($invitation->getMerchantUserRoleId());
        if (!$role) {
            throw new RoleNotFoundException();
        }

        $merchant = $this->merchantRepository->getOneById($invitation->getMerchantId());
        if (!$merchant) {
            throw new MerchantNotFoundException('Merchant not found');
        }

        $this->messageBus->dispatch(
            (new MerchantUserInvitationCreated())
                ->setMerchantPaymentUuid($merchant->getPaymentUuid())
                ->setEmail($invitation->getEmail())
                ->setUserRoleName($role->getName())
                ->setToken($invitation->getToken())
        );
    }
}
