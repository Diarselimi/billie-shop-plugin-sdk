<?php

namespace App\DomainModel\MerchantDebtor;

use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Merchant\MerchantEntity;

class MerchantDebtorRegistrationService
{
    private $merchantDebtorRepository;

    private $merchantDebtorEntityFactory;

    private $paymentsService;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantDebtorEntityFactory $merchantDebtorEntityFactory,
        PaymentsServiceInterface $paymentsService
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantDebtorEntityFactory = $merchantDebtorEntityFactory;
        $this->paymentsService = $paymentsService;
    }

    public function registerMerchantDebtor(DebtorCompany $debtorCompany, MerchantEntity $merchant): MerchantDebtorEntity
    {
        $paymentDebtor = $this->paymentsService->registerDebtor($merchant->getPaymentUuid());

        $merchantDebtor = $this->merchantDebtorEntityFactory->create(
            $debtorCompany,
            $merchant->getId(),
            $paymentDebtor->getPaymentDebtorId()
        );
        $this->merchantDebtorRepository->insert($merchantDebtor);

        return $merchantDebtor;
    }
}
