<?php

namespace App\DomainModel\Order\OrderContainer;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\DebtorSettings\DebtorSettingsEntity;
use App\DomainModel\DebtorSettings\DebtorSettingsRepositoryInterface;
use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Details\MerchantDebtorDetailsDTO;
use App\DomainModel\MerchantDebtor\Details\MerchantDebtorDetailsRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceEntity;
use App\DomainModel\OrderInvoice\OrderInvoiceRepositoryInterface;
use App\DomainModel\OrderLineItem\OrderLineItemEntity;
use App\DomainModel\OrderLineItem\OrderLineItemRepositoryInterface;
use App\DomainModel\OrderRiskCheck\CheckResultCollection;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckRepositoryInterface;
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

    private $orderFinancialDetailsRepository;

    private $orderDunningStatusService;

    private $orderLineItemRepository;

    private $paymentsService;

    private $debtorSettingsRepository;

    private $companiesService;

    private $orderRiskCheckRepository;

    private MerchantDebtorDetailsRepositoryInterface $merchantDebtorDetailsRepository;

    private OrderInvoiceRepositoryInterface $orderInvoiceRepository;

    private InvoiceServiceInterface $invoiceRepository;

    public function __construct(
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        AddressRepositoryInterface $addressRepository,
        PersonRepositoryInterface $personRepository,
        MerchantRepositoryInterface $merchantRepository,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        OrderFinancialDetailsRepositoryInterface $orderFinancialDetailsRepository,
        OrderDunningStatusService $orderDunningStatusService,
        OrderRiskCheckRepositoryInterface $orderRiskCheckRepository,
        OrderLineItemRepositoryInterface $orderLineItemRepository,
        PaymentsServiceInterface $paymentsService,
        DebtorSettingsRepositoryInterface $debtorSettingsRepository,
        CompaniesServiceInterface $companiesService,
        MerchantDebtorDetailsRepositoryInterface $merchantDebtorDetailsRepository,
        OrderInvoiceRepositoryInterface $orderInvoiceRepository,
        InvoiceServiceInterface $invoiceRepository
    ) {
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
        $this->addressRepository = $addressRepository;
        $this->personRepository = $personRepository;
        $this->merchantRepository = $merchantRepository;
        $this->merchantSettingsRepository = $merchantSettingsRepository;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->orderFinancialDetailsRepository = $orderFinancialDetailsRepository;
        $this->orderDunningStatusService = $orderDunningStatusService;
        $this->orderLineItemRepository = $orderLineItemRepository;
        $this->paymentsService = $paymentsService;
        $this->debtorSettingsRepository = $debtorSettingsRepository;
        $this->orderRiskCheckRepository = $orderRiskCheckRepository;
        $this->companiesService = $companiesService;
        $this->merchantDebtorDetailsRepository = $merchantDebtorDetailsRepository;
        $this->orderInvoiceRepository = $orderInvoiceRepository;
        $this->invoiceRepository = $invoiceRepository;
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

    public function loadInvoices(OrderContainer $orderContainer): InvoiceCollection
    {
        $orderInvoices = $this->orderInvoiceRepository->findByOrderId($orderContainer->getOrder()->getId());
        if (empty($orderInvoices)) {
            return new InvoiceCollection([]);
        }

        $uuids = array_map(fn (OrderInvoiceEntity $orderInvoice) => $orderInvoice->getInvoiceUuid(), $orderInvoices);

        return $this->invoiceRepository->getByParameters(['uuids' => $uuids]);
    }

    public function loadMerchant(OrderContainer $orderContainer): MerchantEntity
    {
        return $this->merchantRepository->getOneById($orderContainer->getOrder()->getMerchantId());
    }

    public function loadMerchantSettings(OrderContainer $orderContainer): MerchantSettingsEntity
    {
        return $this->merchantSettingsRepository->getOneByMerchant($orderContainer->getOrder()->getMerchantId());
    }

    public function loadOrderFinancialDetails(OrderContainer $orderContainer): OrderFinancialDetailsEntity
    {
        return $this->orderFinancialDetailsRepository->getCurrentByOrderId($orderContainer->getOrder()->getId());
    }

    public function loadOrderDunningStatus(OrderContainer $orderContainer): ?string
    {
        return $this->orderDunningStatusService->getStatus($orderContainer->getOrder()->getUuid());
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

    public function loadDebtorDetails(OrderContainer $orderContainer): MerchantDebtorDetailsDTO
    {
        return $this->merchantDebtorDetailsRepository->getMerchantDebtorDetails($orderContainer->getOrder()->getMerchantDebtorId());
    }

    public function loadDebtorSettings(OrderContainer $orderContainer): ?DebtorSettingsEntity
    {
        return $this->debtorSettingsRepository->getOneByCompanyUuid($orderContainer->getDebtorCompany()->getUuid());
    }

    public function loadIdentifiedDebtorCompany(OrderContainer $orderContainer): DebtorCompany
    {
        return $this->companiesService->getDebtor($orderContainer->getMerchantDebtor()->getDebtorId());
    }

    public function loadFailedRiskChecks(OrderContainer $orderContainer): CheckResultCollection
    {
        return $this->orderRiskCheckRepository->findLastFailedRiskChecksByOrderId($orderContainer->getOrder()->getId());
    }
}
