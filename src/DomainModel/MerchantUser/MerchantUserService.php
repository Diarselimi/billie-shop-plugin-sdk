<?php

namespace App\DomainModel\MerchantUser;

use App\DomainModel\Address\AddressEntityFactory;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantCompanyNotFoundException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;

class MerchantUserService
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

    public function getUser(string $uuid, string $email): MerchantUserDTO
    {
        $merchantUser = $this->merchantUserRepository->getOneByUuid($uuid);

        if (!$merchantUser) {
            throw new MerchantUserNotFoundException("Merchant user not found");
        }

        $merchant = $this->merchantRepository->getOneById($merchantUser->getMerchantId());
        $company = $this->companiesService->getDebtor($merchant->getCompanyId());

        if (!$company) {
            throw new MerchantCompanyNotFoundException();
        }

        $role = $this->merchantUserPermissionsService->resolveUserRole($merchantUser);

        return (new MerchantUserDTO($merchantUser, $email, $role))
            ->setMerchantCompanyName($company->getName())
            ->setMerchantCompanyAddress($this->addressEntityFactory->createFromDebtorCompany($company));
    }
}
