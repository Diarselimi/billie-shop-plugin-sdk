<?php

namespace App\DomainModel\MerchantDebtor;

use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorSettings\DebtorSettingsEntityFactory;
use App\DomainModel\DebtorSettings\DebtorSettingsRepositoryInterface;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Merchant\MerchantEntity;

class MerchantDebtorRegistrationService
{
    private $merchantDebtorRepository;

    private $merchantDebtorEntityFactory;

    private $debtorSettingsRepository;

    private $debtorSettingsEntityFactory;

    private $paymentsService;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantDebtorEntityFactory $merchantDebtorEntityFactory,
        DebtorSettingsRepositoryInterface $debtorSettingsRepository,
        DebtorSettingsEntityFactory $debtorSettingsEntityFactory,
        PaymentsServiceInterface $paymentsService
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantDebtorEntityFactory = $merchantDebtorEntityFactory;
        $this->debtorSettingsRepository = $debtorSettingsRepository;
        $this->debtorSettingsEntityFactory = $debtorSettingsEntityFactory;
        $this->paymentsService = $paymentsService;
    }

    public function registerMerchantDebtor(DebtorCompany $debtorCompany, MerchantEntity $merchant): MerchantDebtorEntity
    {
        $registerDebtorDTO = new RegisterDebtorDTO($merchant->getPaymentUuid(), $debtorCompany->getUuid());
        $paymentDebtor = $this->paymentsService->registerDebtor($registerDebtorDTO);

        $merchantDebtor = $this->merchantDebtorEntityFactory->create(
            $debtorCompany,
            $merchant->getId(),
            $paymentDebtor->getPaymentDebtorId()
        );
        $this->merchantDebtorRepository->insert($merchantDebtor);
        if (!$this->debtorSettingsRepository->getOneByCompanyUuid($debtorCompany->getUuid())) {
            $debtorSettings = $this->debtorSettingsEntityFactory->create($debtorCompany->getUuid());
            $this->debtorSettingsRepository->insert($debtorSettings);
        }

        return $merchantDebtor;
    }
}
