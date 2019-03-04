<?php

namespace App\Application\UseCase\GetMerchantDebtor;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;

class GetMerchantDebtorUseCase
{
    private $merchantDebtorRepository;

    private $paymentsService;

    private $companiesService;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        BorschtInterface $paymentsService,
        CompaniesServiceInterface $companiesService
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->paymentsService = $paymentsService;
        $this->companiesService = $companiesService;
    }

    public function execute(GetMerchantDebtorRequest $request): GetMerchantDebtorResponse
    {
        $merchantDebtor = $this->merchantDebtorRepository->getOneByMerchantExternalId(
            $request->getMerchantDebtorExternalId(),
            $request->getMerchantId(),
            []
        );

        if (!$merchantDebtor) {
            throw new MerchantDebtorNotFoundException();
        }

        $company = $this->companiesService->getDebtor($merchantDebtor->getDebtorId());
        $paymentsDetails = $this->paymentsService->getDebtorPaymentDetails($merchantDebtor->getPaymentDebtorId());

        return new GetMerchantDebtorResponse([
            'id' => $merchantDebtor->getId(),
            'company_id' => $merchantDebtor->getDebtorId(),
            'payment_id' => $merchantDebtor->getPaymentDebtorId(),
            'external_id' => $request->getMerchantDebtorExternalId(),
            'available_limit' => $merchantDebtor->getFinancingLimit(),
            'outstanding_amount' => $paymentsDetails->getOutstandingAmount(),
            'company' => [
                'crefo_id' => $company->getCrefoId(),
                'schufa_id' => $company->getSchufaId(),
                'name' => $company->getName(),
                'address_house' => $company->getAddressHouse(),
                'address_street' => $company->getAddressStreet(),
                'address_city' => $company->getAddressCity(),
                'address_postal_code' => $company->getAddressPostalCode(),
                'address_country' => $company->getAddressCountry(),
            ],
        ]);
    }
}
