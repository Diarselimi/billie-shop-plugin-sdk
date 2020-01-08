<?php

namespace App\Application\UseCase\GetDebtorCompanyLimits;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorLimit\DebtorLimitServiceInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorContainerFactory;

class GetDebtorCompanyLimitsUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $companiesService;

    private $merchantDebtorRepository;

    private $merchantDebtorContainerFactory;

    private $debtorLimitService;

    public function __construct(
        CompaniesServiceInterface $companiesService,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantDebtorContainerFactory $merchantDebtorContainerFactory,
        DebtorLimitServiceInterface $debtorLimitService
    ) {
        $this->companiesService = $companiesService;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantDebtorContainerFactory = $merchantDebtorContainerFactory;
        $this->debtorLimitService = $debtorLimitService;
    }

    public function execute(GetDebtorCompanyLimitsRequest $request): GetDebtorCompanyLimitsResponse
    {
        $this->validateRequest($request);

        $company = $this->companiesService->getDebtorByUuid($request->getUuid());

        if (!$company) {
            throw new MerchantDebtorNotFoundException('Company not found.');
        }

        $merchantDebtors = $this->merchantDebtorRepository->getManyByDebtorCompanyId($company->getId());

        $merchantDebtorContainers = array_map(function (MerchantDebtorEntity $merchantDebtor) {
            return $this->merchantDebtorContainerFactory->create($merchantDebtor);
        }, $merchantDebtors);

        $limit = (!empty($merchantDebtors)) ? reset($merchantDebtorContainers)->getDebtorLimit() :
            $this->debtorLimitService->retrieve($company->getUuid());

        return new GetDebtorCompanyLimitsResponse($company, $limit, ...$merchantDebtorContainers);
    }
}
