<?php

namespace App\Application\UseCase\GetDebtorCompanyLimits;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantDebtorResponse\BaseMerchantDebtorContainer;

class GetDebtorCompanyLimitsUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $companiesService;

    private $merchantDebtorRepository;

    /**
     * @var MerchantDebtorFinancialDetailsRepositoryInterface
     */
    private $financialDetailsRepository;

    public function __construct(
        CompaniesServiceInterface $companiesService,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantDebtorFinancialDetailsRepositoryInterface $financialDetailsRepository
    ) {
        $this->companiesService = $companiesService;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->financialDetailsRepository = $financialDetailsRepository;
    }

    public function execute(GetDebtorCompanyLimitsRequest $request): GetDebtorCompanyLimitsResponse
    {
        $this->validateRequest($request);

        $company = $this->companiesService->getDebtorByUuid($request->getUuid());

        if (!$company) {
            throw new MerchantDebtorNotFoundException('Company not found.');
        }

        $merchantDebtors = $this->merchantDebtorRepository->getManyByDebtorCompanyId($company->getId());
        $merchantDebtorContainers = [];

        foreach ($merchantDebtors as $merchantDebtor) {
            $limits = $this->financialDetailsRepository->getCurrentByMerchantDebtorId($merchantDebtor->getId());
            $merchantDebtorContainers[] = new BaseMerchantDebtorContainer($merchantDebtor, $company, $limits);
        }

        return new GetDebtorCompanyLimitsResponse($company, ...$merchantDebtorContainers);
    }
}
