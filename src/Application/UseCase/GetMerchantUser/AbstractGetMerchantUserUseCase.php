<?php

namespace App\Application\UseCase\GetMerchantUser;

use App\Application\UseCase\CreateMerchant\Exception\MerchantCompanyNotFoundException;
use App\Application\UseCase\MerchantUserLogin\MerchantUserLoginException;
use App\DomainModel\Address\AddressEntityFactory;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserPermissionsService;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;

abstract class AbstractGetMerchantUserUseCase
{
    protected $merchantUserRepository;

    protected $merchantRepository;

    protected $companiesService;

    protected $addressEntityFactory;

    protected $merchantUserPermissionsService;

    public function __construct(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        AddressEntityFactory $addressEntityFactory,
        MerchantUserPermissionsService $merchantUserPermissionsService
    ) {
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantRepository = $merchantRepository;
        $this->companiesService = $companiesService;
        $this->addressEntityFactory = $addressEntityFactory;
        $this->merchantUserPermissionsService = $merchantUserPermissionsService;
    }

    protected function getUser(GetMerchantUserRequest $request, string $userEmail): GetMerchantUserResponse
    {
        $merchantUser = $this->merchantUserRepository->getOneByUuid($request->getUuid());

        if (!$merchantUser) {
            throw new MerchantUserLoginException("Merchant user not found");
        }

        $merchant = $this->merchantRepository->getOneById($merchantUser->getMerchantId());
        $company = $this->companiesService->getDebtor($merchant->getCompanyId());

        if (!$company) {
            throw new MerchantCompanyNotFoundException();
        }

        $role = $this->merchantUserPermissionsService->resolveUserRole($merchantUser);

        return (new GetMerchantUserResponse($merchantUser, $userEmail, $role))
            ->setMerchantCompanyName($company->getName())
            ->setMerchantCompanyAddress($this->addressEntityFactory->createFromDebtorCompany($company));
    }
}
