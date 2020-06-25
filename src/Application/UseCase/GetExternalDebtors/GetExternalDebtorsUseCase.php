<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetExternalDebtors;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;

class GetExternalDebtorsUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $companiesService;

    public function __construct(CompaniesServiceInterface $companiesService)
    {
        $this->companiesService = $companiesService;
    }

    public function execute(GetExternalDebtorsRequest $request): GetExternalDebtorsResponse
    {
        $this->validateRequest($request);

        try {
            $result = $this->companiesService->searchExternalDebtors($request->getSearchString(), $request->getLimit());
        } catch (CompaniesServiceRequestException $exception) {
            throw new GetExternalDebtorsUseCaseException($exception->getMessage());
        }

        return new GetExternalDebtorsResponse($result);
    }
}
