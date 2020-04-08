<?php

namespace App\DomainModel\MerchantDebtorResponse;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestRepositoryInterface;
use App\DomainModel\DebtorLimit\DebtorLimitServiceInterface;
use App\DomainModel\DebtorLimit\DebtorLimitServiceRequestException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\Payment\PaymentsServiceInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class MerchantDebtorContainerFactory implements LoggingInterface
{
    use LoggingTrait;

    private $merchantDebtorRepository;

    private $merchantRepository;

    private $paymentService;

    private $companiesService;

    private $debtorLimitService;

    private $debtorInformationChangeRequestRepository;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantRepositoryInterface $merchantRepository,
        PaymentsServiceInterface $paymentService,
        CompaniesServiceInterface $companiesService,
        DebtorLimitServiceInterface $debtorLimitService,
        DebtorInformationChangeRequestRepositoryInterface $debtorInformationChangeRequestRepository
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantRepository = $merchantRepository;
        $this->paymentService = $paymentService;
        $this->companiesService = $companiesService;
        $this->debtorLimitService = $debtorLimitService;
        $this->debtorInformationChangeRequestRepository = $debtorInformationChangeRequestRepository;
    }

    public function create(MerchantDebtorEntity $merchantDebtor): MerchantDebtorContainer
    {
        $merchant = $this->merchantRepository->getOneById($merchantDebtor->getMerchantId());

        $externalId = $this->merchantDebtorRepository->findExternalId($merchantDebtor->getId());

        $company = $this->companiesService->getDebtor($merchantDebtor->getDebtorId());

        try {
            $debtorLimit = $this->debtorLimitService->retrieve($company->getUuid());
        } catch (DebtorLimitServiceRequestException $exception) {
            $this->logError('Request to limit service failed: ' . $exception->getMessage());
            $debtorLimit = null;
        }

        $paymentsDetails = $this->paymentService->getDebtorPaymentDetails($merchantDebtor->getPaymentDebtorId());

        $totalCreatedOrdersAmount = $this->merchantDebtorRepository
            ->getMerchantDebtorOrdersAmountByState($merchantDebtor->getId(), OrderStateManager::STATE_CREATED);

        $totalLateOrdersAmount = $this->merchantDebtorRepository
            ->getMerchantDebtorOrdersAmountByState($merchantDebtor->getId(), OrderStateManager::STATE_LATE);

        $debtorInformationChangeRequest = $this
            ->debtorInformationChangeRequestRepository
            ->getNotSeenRequestByCompanyUuid($company->getUuid());

        return new MerchantDebtorContainer(
            $merchantDebtor,
            $merchant,
            $company,
            $debtorLimit,
            $paymentsDetails,
            $totalCreatedOrdersAmount,
            $totalLateOrdersAmount,
            $externalId,
            $debtorInformationChangeRequest
        );
    }
}
