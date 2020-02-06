<?php

namespace App\DomainModel\Order\OrderContainer;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use App\DomainModel\OrderLineItem\OrderLineItemEntity;
use App\DomainModel\OrderLineItem\OrderLineItemRepositoryInterface;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntity;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckRepositoryInterface;
use App\DomainModel\Payment\OrderPaymentDetailsDTO;
use App\DomainModel\Payment\PaymentsServiceInterface;
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

    private $companyService;

    private $orderFinancialDetailsRepository;

    private $orderDunningStatusService;

    private $orderRiskCheckRepository;

    private $orderLineItemRepository;

    private $paymentsService;

    public function __construct(
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        AddressRepositoryInterface $addressRepository,
        PersonRepositoryInterface $personRepository,
        MerchantRepositoryInterface $merchantRepository,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        CompaniesServiceInterface $companiesService,
        OrderFinancialDetailsRepositoryInterface $orderFinancialDetailsRepository,
        OrderDunningStatusService $orderDunningStatusService,
        OrderRiskCheckRepositoryInterface $orderRiskCheckRepository,
        OrderLineItemRepositoryInterface $orderLineItemRepository,
        PaymentsServiceInterface $paymentsService
    ) {
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
        $this->addressRepository = $addressRepository;
        $this->personRepository = $personRepository;
        $this->merchantRepository = $merchantRepository;
        $this->merchantSettingsRepository = $merchantSettingsRepository;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->companyService = $companiesService;
        $this->orderFinancialDetailsRepository = $orderFinancialDetailsRepository;
        $this->orderDunningStatusService = $orderDunningStatusService;
        $this->orderRiskCheckRepository = $orderRiskCheckRepository;
        $this->orderLineItemRepository = $orderLineItemRepository;
        $this->paymentsService = $paymentsService;
    }

    public function loadMerchantDebtor(OrderContainer $orderContainer): MerchantDebtorEntity
    {
        return $this->merchantDebtorRepository->getOneById($orderContainer->getOrder()->getMerchantDebtorId());
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

    public function loadBillingAddress(OrderContainer $orderContainer): AddressEntity
    {
        return $this->addressRepository->getOneById($orderContainer->getDebtorExternalData()->getBillingAddressId());
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

    public function loadOrderDunningStatus(OrderContainer $orderContainer): ? string
    {
        return $this->orderDunningStatusService->getStatus($orderContainer->getOrder()->getUuid());
    }

    /**
     * @param OrderContainer $orderContainer
     *
     * @return OrderRiskCheckEntity[]
     */
    public function loadOrderRiskChecks(OrderContainer $orderContainer): array
    {
        return $this->orderRiskCheckRepository->findByOrder($orderContainer->getOrder());
    }

    /**
     * @param OrderContainer $orderContainer
     *
     * @return OrderLineItemEntity[]
     */
    public function loadOrderLineItems(OrderContainer $orderContainer): array
    {
        return $this->orderLineItemRepository->getByOrderId($orderContainer->getOrder()->getId());
    }

    public function loadPaymentDetails(OrderContainer $orderContainer): OrderPaymentDetailsDTO
    {
        return $this->paymentsService->getOrderPaymentDetails($orderContainer->getOrder()->getPaymentId());
    }
}
