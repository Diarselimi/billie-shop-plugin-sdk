<?php

namespace App\Application\UseCase\GetMerchantDebtor;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorContainer;
use App\DomainModel\Order\OrderStateManager;

class GetMerchantDebtorUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantDebtorRepository;

    private $paymentService;

    private $companiesService;

    private $financialDetailsRepository;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        BorschtInterface $paymentService,
        CompaniesServiceInterface $companiesService,
        MerchantDebtorFinancialDetailsRepositoryInterface $financialDetailsRepository
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->paymentService = $paymentService;
        $this->companiesService = $companiesService;
        $this->financialDetailsRepository = $financialDetailsRepository;
    }

    /**
     * @param  GetMerchantDebtorRequest $request
     * @return MerchantDebtorContainer
     */
    public function execute(GetMerchantDebtorRequest $request): MerchantDebtorContainer
    {
        $this->validateRequest($request);

        $merchantDebtor = $request->getMerchantId() ?
            $this->merchantDebtorRepository->getOneByUuidAndMerchantId(
                $request->getMerchantDebtorUuid(),
                $request->getMerchantId()
            ) : $this->merchantDebtorRepository->getOneByUuid(
                $request->getMerchantDebtorUuid()
            );

        if (!$merchantDebtor) {
            throw new MerchantDebtorNotFoundException();
        }

        $externalId = $this->merchantDebtorRepository->findExternalId($merchantDebtor->getId());

        $financingDetails = $this->financialDetailsRepository->getCurrentByMerchantDebtorId($merchantDebtor->getId());
        $company = $this->companiesService->getDebtor($merchantDebtor->getDebtorId());
        $paymentsDetails = $this->paymentService->getDebtorPaymentDetails($merchantDebtor->getPaymentDebtorId());

        $createdOutstandingAmount = $this->merchantDebtorRepository
            ->getMerchantDebtorOrdersAmountByState($merchantDebtor->getId(), OrderStateManager::STATE_CREATED);

        $lateOutstandingAmount = $this->merchantDebtorRepository
            ->getMerchantDebtorOrdersAmountByState($merchantDebtor->getId(), OrderStateManager::STATE_LATE);

        return new MerchantDebtorContainer(
            $externalId,
            $merchantDebtor,
            $company,
            $paymentsDetails,
            $financingDetails,
            $createdOutstandingAmount,
            $lateOutstandingAmount
        );
    }
}
