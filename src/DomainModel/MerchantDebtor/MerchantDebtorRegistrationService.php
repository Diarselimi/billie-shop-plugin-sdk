<?php

namespace App\DomainModel\MerchantDebtor;

use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;

class MerchantDebtorRegistrationService
{
    private $merchantDebtorRepository;

    private $merchantDebtorEntityFactory;

    private $merchantSettingsRepository;

    private $merchantDebtorFinancingDetailsEntityFactory;

    private $merchantDebtorFinancialDetailsRepository;

    private $paymentsService;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantDebtorEntityFactory $merchantDebtorEntityFactory,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        MerchantDebtorFinancingDetailsEntityFactory $merchantDebtorFinancingDetailsEntityFactory,
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository,
        BorschtInterface $paymentsService
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantDebtorEntityFactory = $merchantDebtorEntityFactory;
        $this->merchantDebtorFinancingDetailsEntityFactory = $merchantDebtorFinancingDetailsEntityFactory;
        $this->merchantDebtorFinancialDetailsRepository = $merchantDebtorFinancialDetailsRepository;
        $this->merchantSettingsRepository = $merchantSettingsRepository;
        $this->paymentsService = $paymentsService;
    }

    public function registerMerchantDebtor(string $debtorCompanyId, MerchantEntity $merchant): MerchantDebtorEntity
    {
        $paymentDebtor = $this->paymentsService->registerDebtor($merchant->getPaymentMerchantId());

        $merchantSettings = $this->merchantSettingsRepository->getOneByMerchant($merchant->getId());

        $merchantDebtor = $this->merchantDebtorEntityFactory->create(
            $debtorCompanyId,
            $merchant->getId(),
            $paymentDebtor->getPaymentDebtorId()
        );
        $this->merchantDebtorRepository->insert($merchantDebtor);

        $merchantDebtorFinancialDetails = $this->merchantDebtorFinancingDetailsEntityFactory->create(
            $merchantDebtor->getId(),
            $merchantSettings->getInitialDebtorFinancingLimit(),
            $merchantSettings->getInitialDebtorFinancingLimit()
        );
        $this->merchantDebtorFinancialDetailsRepository->insert($merchantDebtorFinancialDetails);

        return $merchantDebtor;
    }
}
