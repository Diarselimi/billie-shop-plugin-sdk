<?php

namespace App\Application\UseCase\SignatoryPowersSelection;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\DomainModel\SignatoryPowersSelection\SignatoryPowerDTO;

class SignatoryPowersSelectionUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $companiesService;

    private $merchantUserRepository;

    public function __construct(
        CompaniesServiceInterface $companiesService,
        MerchantUserRepositoryInterface $merchantUserRepository
    ) {
        $this->companiesService = $companiesService;
        $this->merchantUserRepository = $merchantUserRepository;
    }

    public function execute(SignatoryPowersSelectionRequest $selectionsRequest): void
    {
        $this->validateRequest($selectionsRequest);

        try {
            $this->companiesService->saveSelectedSignatoryPowers(
                $selectionsRequest->getCompanyId(),
                ...$selectionsRequest->getSignatoryPowers()
            );
        } catch (CompaniesServiceRequestException $exception) {
            throw new SignatoryPowersSelectionException();
        }

        $loggedInSignatory = $selectionsRequest->findSelectedAsLoggedInSignatory();
        if ($loggedInSignatory) {
            $this->merchantUserRepository->assignSignatoryPowerToUser(
                $selectionsRequest->getMerchantUserId(),
                $loggedInSignatory->getUuid()
            );
        }
    }
}
