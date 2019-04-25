<?php

namespace App\Application\UseCase\GetMerchantDebtor;

use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;

class GetMerchantDebtorResponseFactory
{
    private $companiesService;

    private $paymentService;

    private $merchantDebtorRepository;

    public function __construct(
        BorschtInterface $paymentService,
        CompaniesServiceInterface $companiesService,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository
    ) {
        $this->paymentService = $paymentService;
        $this->companiesService = $companiesService;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
    }

    public function create(
        MerchantDebtorEntity $merchantDebtor,
        string $merchantDebtorExternalId
    ) {
        $company = $this->companiesService->getDebtor($merchantDebtor->getDebtorId());
        $paymentsDetails = $this->paymentService->getDebtorPaymentDetails($merchantDebtor->getPaymentDebtorId());
        $createdAmount = $this->merchantDebtorRepository->getMerchantDebtorCreatedOrdersAmount($merchantDebtor->getId());

        return new GetMerchantDebtorResponse([
            'id' => $merchantDebtor->getId(),
            'company_id' => $merchantDebtor->getDebtorId(),
            'payment_id' => $merchantDebtor->getPaymentDebtorId(),
            'external_id' => $merchantDebtorExternalId,
            'available_limit' => $merchantDebtor->getFinancingLimit(),
            'total_limit' => $merchantDebtor->getFinancingLimit() + $createdAmount + $paymentsDetails->getOutstandingAmount(),
            'created_amount' => $createdAmount,
            'outstanding_amount' => $paymentsDetails->getOutstandingAmount(),
            'is_whitelisted' => $merchantDebtor->isWhitelisted(),
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
