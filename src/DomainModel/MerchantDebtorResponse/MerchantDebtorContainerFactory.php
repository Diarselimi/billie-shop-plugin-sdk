<?php

namespace App\DomainModel\MerchantDebtorResponse;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorLimit\DebtorLimitServiceInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\Payment\PaymentsServiceInterface;

class MerchantDebtorContainerFactory
{
    private $merchantDebtorRepository;

    private $merchantRepository;

    private $paymentService;

    private $companiesService;

    private $debtorLimitService;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantRepositoryInterface $merchantRepository,
        PaymentsServiceInterface $paymentService,
        CompaniesServiceInterface $companiesService,
        DebtorLimitServiceInterface $debtorLimitService
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantRepository = $merchantRepository;
        $this->paymentService = $paymentService;
        $this->companiesService = $companiesService;
        $this->debtorLimitService = $debtorLimitService;
    }

    public function create(MerchantDebtorEntity $merchantDebtor): MerchantDebtorContainer
    {
        $merchant = $this->merchantRepository->getOneById($merchantDebtor->getMerchantId());

        $externalId = $this->merchantDebtorRepository->findExternalId($merchantDebtor->getId());

        $company = $this->companiesService->getDebtor($merchantDebtor->getDebtorId());

        $debtorLimit = $this->debtorLimitService->retrieve($company->getUuid());

        $paymentsDetails = $this->paymentService->getDebtorPaymentDetails($merchantDebtor->getPaymentDebtorId());

        $totalCreatedOrdersAmount = $this->merchantDebtorRepository
            ->getMerchantDebtorOrdersAmountByState($merchantDebtor->getId(), OrderStateManager::STATE_CREATED);

        $totalLateOrdersAmount = $this->merchantDebtorRepository
            ->getMerchantDebtorOrdersAmountByState($merchantDebtor->getId(), OrderStateManager::STATE_LATE);

        return new MerchantDebtorContainer(
            $merchantDebtor,
            $merchant,
            $company,
            $debtorLimit,
            $paymentsDetails,
            $totalCreatedOrdersAmount,
            $totalLateOrdersAmount,
            $externalId
        );
    }
}
