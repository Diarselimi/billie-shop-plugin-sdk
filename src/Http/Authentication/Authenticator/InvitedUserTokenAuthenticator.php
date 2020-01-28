<?php

namespace App\Http\Authentication\Authenticator;

use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationRepositoryInterface;
use App\Http\Authentication\InvitedUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class InvitedUserTokenAuthenticator extends AbstractAuthenticator
{
    private $invitationRepository;

    public function __construct(
        MerchantUserInvitationRepositoryInterface $invitationRepository,
        MerchantRepositoryInterface $merchantRepository
    ) {
        $this->invitationRepository = $invitationRepository;
        parent::__construct($merchantRepository);
    }

    public function supports(Request $request)
    {
        return $request->attributes->has('token');
    }

    public function getCredentials(Request $request)
    {
        return $request->attributes->get('token');
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $invitation = $this->invitationRepository->findValidByToken($credentials);

        if (!$invitation) {
            throw new AuthenticationException();
        }

        $merchant = $this->assertValidMerchant($this->merchantRepository->getOneById($invitation->getMerchantId()));

        return new InvitedUser($merchant, $invitation);
    }
}
