<?php

namespace App\DomainModel\Order\OrderContainer;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorFinancialDetailsEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use App\DomainModel\Person\PersonEntity;
use App\DomainModel\Person\PersonRepositoryInterface;

class OrderContainerRelationLoader
{
    private $personRepository;

    private $addressRepository;

    private $debtorExternalDataRepository;

    private $merchantRepository;

    private $merchantSettingsRepository;

    private $merchantDebtorRepository;

    private $merchantDebtorFinancialDetailsRepository;

    private $companyService;

    private $orderFinancialDetailsRepository;

    public function __construct(
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        AddressRepositoryInterface $addressRepository,
        PersonRepositoryInterface $personRepository,
        MerchantRepositoryInterface $merchantRepository,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository,
        CompaniesServiceInterface $companiesService,
        OrderFinancialDetailsRepositoryInterface $orderFinancialDetailsRepository
    ) {
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
        $this->addressRepository = $addressRepository;
        $this->personRepository = $personRepository;
        $this->merchantRepository = $merchantRepository;
        $this->merchantSettingsRepository = $merchantSettingsRepository;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantDebtorFinancialDetailsRepository = $merchantDebtorFinancialDetailsRepository;
        $this->companyService = $companiesService;
        $this->orderFinancialDetailsRepository = $orderFinancialDetailsRepository;
    }

    public function loadMerchantDebtor(OrderContainer $orderContainer): MerchantDebtorEntity
    {
        return $this->merchantDebtorRepository->getOneById($orderContainer->getOrder()->getMerchantDebtorId());
    }

    public function loadMerchantDebtorFinancialDetails(OrderContainer $orderContainer): MerchantDebtorFinancialDetailsEntity
    {
        return $this->merchantDebtorFinancialDetailsRepository->getCurrentByMerchantDebtorId($orderContainer->getOrder()->getMerchantDebtorId());
    }

    public function loadDebtorPerson(OrderContainer $orderContainer): PersonEntity
    {
        return $this->personRepository->getOneById($orderContainer->getOrder()->getDebtorPersonId());
    }

    public function loadDebtorExternalData(OrderContainer $orderContainer): DebtorExternalDataEntity
    {
        return $this->debtorExternalDataRepository->getOneById($orderContainer->getOrder()->getDebtorExternalDataId());
    }

    public function loadDebtorExternalDataAddress(OrderContainer $orderContainer): AddressEntity
    {
        return $this->addressRepository->getOneById($orderContainer->getDebtorExternalData()->getAddressId());
    }

    public function loadDeliveryAddress(OrderContainer $orderContainer): AddressEntity
    {
        return $this->addressRepository->getOneById($orderContainer->getOrder()->getDeliveryAddressId());
    }

    public function loadMerchant(OrderContainer $orderContainer): MerchantEntity
    {
        return $this->merchantRepository->getOneById($orderContainer->getOrder()->getMerchantId());
    }

    public function loadMerchantSettings(OrderContainer $orderContainer): MerchantSettingsEntity
    {
        return $this->merchantSettingsRepository->getOneByMerchant($orderContainer->getOrder()->getMerchantId());
    }

    public function loadDebtorCompany(OrderContainer $orderContainer): DebtorCompany
    {
        return $this->companyService->getDebtor($orderContainer->getMerchantDebtor()->getDebtorId());
    }

    public function loadOrderFinancialDetails(OrderContainer $orderContainer): OrderFinancialDetailsEntity
    {
        return $this->orderFinancialDetailsRepository->getCurrentByOrderId($orderContainer->getOrder()->getId());
    }
}
