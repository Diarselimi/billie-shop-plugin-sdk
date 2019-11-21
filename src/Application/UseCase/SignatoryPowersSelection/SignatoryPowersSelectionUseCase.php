<?php

namespace App\Application\UseCase\SignatoryPowersSelection;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;

class SignatoryPowersSelectionUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $companiesService;

    public function __construct(CompaniesServiceInterface $companiesService)
    {
        $this->companiesService = $companiesService;
    }

    public function execute(SignatoryPowersSelectionRequest $signatoryPowersSelectionRequest): void
    {
        $this->validateRequest($signatoryPowersSelectionRequest);

        try {
            $this->companiesService->saveSelectedSignatoryPowers(
                $signatoryPowersSelectionRequest->getCompanyId(),
                ...$signatoryPowersSelectionRequest->getSignatoryPowers()
            );
        } catch (CompaniesServiceRequestException $exception) {
            throw new SignatoryPowersSelectionException();
        }
    }
}
