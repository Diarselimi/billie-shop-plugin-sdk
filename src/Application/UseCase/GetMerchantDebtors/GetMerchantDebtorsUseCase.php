<?php

namespace App\Application\UseCase\GetMerchantDebtors;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorList;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorListItem;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorResponseFactory;

class GetMerchantDebtorsUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantDebtorRepository;

    private $companiesService;

    private $financialDetailsRepository;

    private $responseFactory;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        CompaniesServiceInterface $companiesService,
        MerchantDebtorFinancialDetailsRepositoryInterface $financialDetailsRepository,
        MerchantDebtorResponseFactory $responseFactory
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->responseFactory = $responseFactory;
        $this->companiesService = $companiesService;
        $this->financialDetailsRepository = $financialDetailsRepository;
    }

    public function execute(GetMerchantDebtorsRequest $request): MerchantDebtorList
    {
        $this->validateRequest($request);

        $result = $this->merchantDebtorRepository->getByMerchantId(
            $request->getMerchantId(),
            $request->getOffset(),
            $request->getLimit(),
            $request->getSortBy(),
            $request->getSortDirection(),
            $request->getSearchString()
        );

        $merchantDebtors = array_map(function (array $row) use ($request) {
            return $this->createListItem($row['id'], $row['external_id'], $row['debtor_id']);
        }, $result['rows']);

        return $this->responseFactory->createList($result['total'], $merchantDebtors);
    }

    private function createListItem(int $id, string $externalId, int $companyId): MerchantDebtorListItem
    {
        $merchantDebtor = $this->merchantDebtorRepository->getOneById($id);

        if (!$merchantDebtor) {
            throw new MerchantDebtorNotFoundException();
        }

        $financingDetails = $this->financialDetailsRepository->getCurrentByMerchantDebtorId($id);
        $company = $this->companiesService->getDebtor($companyId);

        return $this->responseFactory->createListItem($externalId, $merchantDebtor, $company, $financingDetails);
    }
}
