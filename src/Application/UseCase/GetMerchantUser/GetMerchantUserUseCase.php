<?php

namespace App\Application\UseCase\GetMerchantUser;

use App\DomainModel\Address\AddressEntityFactory;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserPermissionsService;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\Http\Authentication\User;
use Symfony\Component\Security\Core\Security;

class GetMerchantUserUseCase
{
    private $merchantUserRepository;

    private $merchantRepository;

    private $companiesService;

    private $addressEntityFactory;

    private $security;

    private $merchantUserPermissionsService;

    public function __construct(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        AddressEntityFactory $addressEntityFactory,
        MerchantUserPermissionsService $merchantUserPermissionsService,
        Security $security
    ) {
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantRepository = $merchantRepository;
        $this->companiesService = $companiesService;
        $this->addressEntityFactory = $addressEntityFactory;
        $this->security = $security;
        $this->merchantUserPermissionsService = $merchantUserPermissionsService;
    }

    public function execute(GetMerchantUserRequest $request): GetMerchantUserResponse
    {
        $merchantUser = $this->merchantUserRepository->getOneByUserId($request->getUserId());

        if (!$merchantUser) {
            throw new MerchantUserNotFoundException();
        }

        $merchant = $this->merchantRepository->getOneById($merchantUser->getMerchantId());
        $company = $this->companiesService->getDebtor($merchant->getCompanyId());
        /** @var User $user */
        $user = $this->security->getUser();
        $role = $this->merchantUserPermissionsService->resolveUserRole($merchantUser);

        return (new GetMerchantUserResponse())
            ->setUserId($merchant->getId())
            ->setPermissions($role->getPermissions())
            ->setRole($role->getName())
            ->setFirstName($merchantUser->getFirstName())
            ->setLastName($merchantUser->getLastName())
            ->setEmail($user->getEmail())
            ->setMerchantCompanyName($company->getName())
            ->setMerchantCompanyAddress($this->addressEntityFactory->createFromDebtorCompany($company));
    }
}
