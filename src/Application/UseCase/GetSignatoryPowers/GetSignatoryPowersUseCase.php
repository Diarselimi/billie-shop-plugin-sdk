<?php

namespace App\Application\UseCase\GetSignatoryPowers;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\GetSignatoryPowers\GetSignatoryPowersResponse;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\DomainModel\SignatoryPowersSelection\UserSignatoryPowerMatcher;

class GetSignatoryPowersUseCase
{
    private $companiesService;

    private $merchantUserRepository;

    private $merchantRepository;

    private $signatoryPowerMatcher;

    public function __construct(
        CompaniesServiceInterface $companiesService,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantRepositoryInterface $merchantRepository,
        UserSignatoryPowerMatcher $signatoryPowerMatcher
    ) {
        $this->companiesService = $companiesService;
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantRepository = $merchantRepository;
        $this->signatoryPowerMatcher = $signatoryPowerMatcher;
    }

    public function execute(GetSignatoryPowersRequest $request): GetSignatoryPowersResponse
    {
        $merchant = $this->merchantRepository->getOneById($request->getMerchantId());
        $merchantUser = $this->merchantUserRepository->getOneByUuid($request->getUserUuid());

        try {
            $signatoryPowersDTOs = $this->companiesService->getSignatoryPowers($merchant->getCompanyId());
        } catch (CompaniesServiceRequestException $exception) {
            throw new GetSignatoryPowersUseCaseException($exception->getMessage());
        }

        $this->signatoryPowerMatcher->identify($merchantUser, ...$signatoryPowersDTOs);

        return new GetSignatoryPowersResponse($signatoryPowersDTOs);
    }
}
