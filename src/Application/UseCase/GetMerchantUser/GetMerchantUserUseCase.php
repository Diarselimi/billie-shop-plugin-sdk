<?php

namespace App\Application\UseCase\GetMerchantUser;

use App\DomainModel\Address\AddressEntityFactory;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;

class GetMerchantUserUseCase
{
    private $merchantUserRepository;

    private $merchantRepository;

    private $companiesService;

    private $addressEntityFactory;

    public function __construct(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        AddressEntityFactory $addressEntityFactory
    ) {
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantRepository = $merchantRepository;
        $this->companiesService = $companiesService;
        $this->addressEntityFactory = $addressEntityFactory;
    }

    public function execute(GetMerchantUserRequest $request): GetMerchantUserResponse
    {
        $merchantUser = $this->merchantUserRepository->getOneByUserId($request->getUserId());

        if (!$merchantUser) {
            throw new MerchantUserNotFoundException();
        }

        $merchant = $this->merchantRepository->getOneById($merchantUser->getMerchantId());
        $company = $this->companiesService->getDebtor($merchant->getCompanyId());

        return (new GetMerchantUserResponse())
            ->setUserId($merchant->getId())
            ->setRoles($merchantUser->getRoles())
            ->setFirstName($merchantUser->getFirstName())
            ->setLastName($merchantUser->getLastName())
            ->setMerchantCompanyName($company->getName())
            ->setMerchantCompanyAddress($this->addressEntityFactory->createFromDebtorCompany($company))
            ;
    }
}
