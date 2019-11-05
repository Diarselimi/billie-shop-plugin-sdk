<?php

namespace App\Application\UseCase\GetMerchantUser;

use App\DomainModel\Address\AddressEntityFactory;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserPermissionsService;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\Http\Authentication\User;
use Symfony\Component\Security\Core\Security;

class GetMerchantUserUseCase extends AbstractGetMerchantUserUseCase
{
    private $security;

    public function __construct(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        AddressEntityFactory $addressEntityFactory,
        MerchantUserPermissionsService $merchantUserPermissionsService,
        Security $security
    ) {
        parent::__construct(
            $merchantUserRepository,
            $merchantRepository,
            $companiesService,
            $addressEntityFactory,
            $merchantUserPermissionsService
        );
        $this->security = $security;
    }

    public function execute(GetMerchantUserRequest $request): GetMerchantUserResponse
    {
        /** @var User $user */
        $user = $this->security->getUser();

        return $this->getUser($request, $user->getEmail());
    }
}
