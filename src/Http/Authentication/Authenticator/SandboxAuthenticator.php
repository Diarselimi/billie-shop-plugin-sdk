<?php

namespace App\Http\Authentication\Authenticator;

use App\Application\UseCase\AuthorizeSandbox\AuthorizeSandboxDTO;
use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\MerchantUserPermissions;
use App\DomainModel\MerchantUser\MerchantUserPermissionsService;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\DomainModel\Sandbox\ProdAccessClientInterface;
use App\DomainModel\Sandbox\ProdAccessRequestException;
use App\Http\Authentication\MerchantUser;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SandboxAuthenticator extends OAuthTokenAuthenticator
{
    private $prodAccessClient;

    private $paellaSandboxUrl;

    public function __construct(
        ProdAccessClientInterface $prodAccessClient,
        string $paellaSandboxUrl,
        AuthenticationServiceInterface $authenticationService,
        MerchantRepository $merchantRepository,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserPermissionsService $merchantUserPermissionsService
    ) {
        $this->prodAccessClient = $prodAccessClient;
        $this->paellaSandboxUrl = $paellaSandboxUrl;

        parent::__construct($authenticationService, $merchantRepository, $merchantUserRepository, $merchantUserPermissionsService);
    }

    public function supports(Request $request)
    {
        if ($this->wasAlreadyAuthenticated($request)) {
            return false;
        }

        return $request->headers->has(HttpConstantsInterface::REQUEST_HEADER_AUTHORIZATION);
    }

    public function getCredentials(Request $request)
    {
        return $request->headers->get(HttpConstantsInterface::REQUEST_HEADER_AUTHORIZATION);
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            $sandboxLoginDTO = $this->prodAccessClient->authorizeTokenForSandbox($credentials);
        } catch (ProdAccessRequestException $exception) {
            return parent::getUser($credentials, $userProvider);
        }

        return $this->authenticateAsMerchantUser($sandboxLoginDTO);
    }

    private function authenticateAsMerchantUser(AuthorizeSandboxDTO $sandboxLoginDTO): UserInterface
    {
        if (!in_array(MerchantUserPermissions::ACCESS_TEST_DATA, $sandboxLoginDTO->getUserEntity()->getPermissions())) {
            throw new AccessDeniedException();
        }

        $merchant = $this->assertValidMerchant(
            $this->merchantRepository->getOneByPaymentUuid($sandboxLoginDTO->getSandboxMerchantPaymentUuid())
        );

        return new MerchantUser(
            $merchant,
            $sandboxLoginDTO->getEmail(),
            $sandboxLoginDTO->getUserEntity(),
            $sandboxLoginDTO->getUserEntity()->getPermissions()
        );
    }
}
