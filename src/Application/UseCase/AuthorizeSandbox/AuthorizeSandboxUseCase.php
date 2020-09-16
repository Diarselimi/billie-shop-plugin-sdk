<?php

declare(strict_types=1);

namespace App\Application\UseCase\AuthorizeSandbox;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\DomainModel\MerchantUser\MerchantUserPermissionsService;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\DomainModel\MerchantUser\RoleNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthorizeSandboxUseCase
{
    private $authenticationService;

    private $merchantRepository;

    private $merchantUserRepository;

    private $merchantUserPermissionsService;

    public function __construct(
        AuthenticationServiceInterface $authenticationService,
        MerchantRepositoryInterface $merchantRepository,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserPermissionsService $merchantUserPermissionsService
    ) {
        $this->authenticationService = $authenticationService;
        $this->merchantRepository = $merchantRepository;
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantUserPermissionsService = $merchantUserPermissionsService;
    }

    public function execute(string $token): AuthorizeSandboxDTO
    {
        $tokenMetadata = $this->authenticationService->authorizeToken($token);
        if ($tokenMetadata === null) {
            throw new AuthenticationException();
        }

        $userEntity = $this->getUserEntity($tokenMetadata->getUserId());
        $merchant = $this->getMerchant($userEntity->getMerchantId());

        return new AuthorizeSandboxDTO($userEntity, $tokenMetadata->getEmail(), $merchant->getSandboxPaymentUuid());
    }

    private function getUserEntity(string $oauthUserId): MerchantUserEntity
    {
        $userEntity = $this->merchantUserRepository->getOneByUuid($oauthUserId);

        if (!$userEntity) {
            throw new AuthenticationException();
        }

        try {
            $permissions = $this->merchantUserPermissionsService->resolveUserRole($userEntity)->getPermissions();
        } catch (RoleNotFoundException $exception) {
            throw new AuthenticationException();
        }

        $userEntity->setPermissions($permissions);

        return $userEntity;
    }

    private function getMerchant(int $merchantId): MerchantEntity
    {
        $merchant = $this->merchantRepository->getOneById($merchantId);

        if (!$merchant) {
            throw new AuthenticationException();
        }

        return $merchant;
    }
}
