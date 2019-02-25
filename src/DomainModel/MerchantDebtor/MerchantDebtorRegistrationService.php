<?php

namespace App\DomainModel\MerchantDebtor;

use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;

class MerchantDebtorRegistrationService
{
    private $merchantDebtorRepository;

    private $merchantDebtorEntityFactory;

    private $merchantSettingsRepository;

    private $paymentsService;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantDebtorEntityFactory $merchantDebtorEntityFactory,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        BorschtInterface $paymentsService
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantDebtorEntityFactory = $merchantDebtorEntityFactory;
        $this->merchantSettingsRepository = $merchantSettingsRepository;
        $this->paymentsService = $paymentsService;
    }

    public function registerMerchantDebtor(string $debtorCompanyId, MerchantEntity $merchant): MerchantDebtorEntity
    {
        $paymentDebtor = $this->paymentsService->registerDebtor($merchant->getPaymentMerchantId());

        $merchantSettings = $this->merchantSettingsRepository->getOneByMerchantOrFail($merchant->getId());

        $merchantDebtor = $this->merchantDebtorEntityFactory->create(
            $debtorCompanyId,
            $merchant->getId(),
            $paymentDebtor->getPaymentDebtorId(),
            $merchantSettings->getDebtorFinancingLimit()
        );

        $this->merchantDebtorRepository->insert($merchantDebtor);

        return $merchantDebtor;
    }
}
