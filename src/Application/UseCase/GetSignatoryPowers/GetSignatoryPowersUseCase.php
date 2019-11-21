<?php

namespace App\Application\UseCase\GetSignatoryPowers;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\GetSignatoryPowers\GetSignatoryPowersResponse;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\Helper\String\StringSearch;

class GetSignatoryPowersUseCase
{
    private $companiesService;

    private $merchantUserRepository;

    private $merchantRepository;

    private $stringSearch;

    public function __construct(
        CompaniesServiceInterface $companiesService,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantRepositoryInterface $merchantRepository,
        StringSearch $stringSearch
    ) {
        $this->companiesService = $companiesService;
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantRepository = $merchantRepository;
        $this->stringSearch = $stringSearch;
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

        foreach ($signatoryPowersDTOs as $signatoryPowersDTO) {
            $isIdentifiedAsUser = $this->stringSearch->areAllWordsInString(
                [$merchantUser->getFirstName(), $merchantUser->getLastName()],
                "{$signatoryPowersDTO->getFirstName()} {$signatoryPowersDTO->getLastName()}"
            );

            if ($isIdentifiedAsUser) {
                $signatoryPowersDTO->setAutomaticallyIdentifiedAsUser($isIdentifiedAsUser);

                break;
            }
        }

        return new GetSignatoryPowersResponse($signatoryPowersDTOs);
    }
}
