<?php

namespace App\DomainModel\OrderResponse;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorCompany\IdentifiedDebtorCompany;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderRiskCheck\CheckResultCollection;
use App\DomainModel\Payment\OrderPaymentDetailsDTO;
use App\DomainModel\Payment\PaymentsServiceInterface;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;

class OrderResponseFactory
{
    private $companiesService;

    private $paymentsService;

    private $orderStateManager;

    private $declinedReasonsMapper;

    public function __construct(
        CompaniesServiceInterface $companiesService,
        PaymentsServiceInterface $paymentsService,
        OrderStateManager $orderStateManager,
        OrderDeclinedReasonsMapper $declinedReasonsMapper
    ) {
        $this->companiesService = $companiesService;
        $this->paymentsService = $paymentsService;
        $this->orderStateManager = $orderStateManager;
        $this->declinedReasonsMapper = $declinedReasonsMapper;
    }

    public function create(OrderContainer $orderContainer): OrderResponse
    {
        $order = $orderContainer->getOrder();
        $response = new OrderResponse();

        $this->addFinancialDetails($orderContainer, $response);
        $this->addOrderData($order, $response);
        $this->addExternalData($orderContainer, $response);
        $this->addDeliveryData($orderContainer, $response);
        $this->addBillingAddressData($orderContainer->getBillingAddress(), $response);

        if ($order->getMerchantDebtorId()) {
            $this->addCompanyData(
                $orderContainer->getDebtorCompany()->getAddress(),
                $orderContainer->getDebtorCompany()->getName(),
                $response
            );
            $this->addPaymentData($orderContainer, $response);
        }

        if ($order->getPaymentId()) {
            $this->addInvoiceData($orderContainer, $response);
        }

        if ($this->orderStateManager->isLate($order)) {
            $response->setDunningStatus($orderContainer->getDunningStatus());
        }

        $response = $this->addReasons($orderContainer->getRiskCheckResultCollection(), $response);

        return $response;
    }

    /**
     * @param  OrderContainer[] $orderContainers
     * @return OrderResponse[]
     */
    public function createFromOrderContainers(array $orderContainers): array
    {
        if (empty($orderContainers)) {
            return [];
        }

        $orderResponses = [];

        $debtorCompanies = $this->getDebtorCompanies($orderContainers);
        $orderPaymentsDetails = $this->getOrderPaymentsDetails($orderContainers);
        foreach ($orderContainers as $orderContainer) {
            if ($orderContainer->getOrder()->getMerchantDebtorId() !== null) {
                $key = $orderContainer->getMerchantDebtor()->getDebtorId();
                $orderContainer->setDebtorCompany($debtorCompanies[$key]);
            }

            if (isset($orderPaymentsDetails[$orderContainer->getOrder()->getPaymentId()])) {
                $orderContainer->setPaymentDetails($orderPaymentsDetails[$orderContainer->getOrder()->getPaymentId()]);
            }

            $orderResponses[] = $this->create($orderContainer);
        }

        return $orderResponses;
    }

    /**
     * @param OrderContainer[]
     * @return OrderPaymentDetailsDTO[]
     */
    public function getOrderPaymentsDetails(array $orderContainers): array
    {
        $paymentIds = array_map(static function (OrderContainer $orderContainer) {
            if ($orderContainer->getOrder()->getPaymentId() !== null) {
                return $orderContainer->getOrder()->getPaymentId();
            }

            return null;
        }, $orderContainers);

        return $this->paymentsService->getBatchOrderPaymentDetails($paymentIds);
    }

    /**
     * @param OrderContainer[]
     * @return DebtorCompany[]
     */
    private function getDebtorCompanies(array $orderContainers): array
    {
        $debtorIds = array_map(static function (OrderContainer $orderContainer) {
            if ($orderContainer->getOrder()->getMerchantDebtorId() !== null) {
                return $orderContainer->getMerchantDebtor()->getDebtorId();
            }

            return null;
        }, $orderContainers);
        $debtorIds = array_filter($debtorIds);

        return $this->companiesService->getDebtors($debtorIds);
    }

    private function addFinancialDetails(OrderContainer $orderContainer, OrderResponse $response): void
    {
        $response
            ->setAmount(TaxedMoneyFactory::create(
                $orderContainer->getOrderFinancialDetails()->getAmountGross(),
                $orderContainer->getOrderFinancialDetails()->getAmountNet(),
                $orderContainer->getOrderFinancialDetails()->getAmountTax()
            ))
            ->setDuration($orderContainer->getOrderFinancialDetails()->getDuration());
    }

    private function addOrderData(OrderEntity $order, OrderResponse $response): void
    {
        $response
            ->setExternalCode($order->getExternalCode())
            ->setUuid($order->getUuid())
            ->setState($order->getState())
            ->setCreatedAt($order->getCreatedAt())
            ->setShippedAt($order->getShippedAt());
    }

    /**
     * @param OrderResponse|CheckoutAuthorizeOrderResponse $response
     */
    private function addCompanyData(AddressEntity $address, string $companyName, $response)
    {
        $response
            ->setCompanyName($companyName)
            ->setCompanyAddressHouseNumber($address->getHouseNumber())
            ->setCompanyAddressStreet($address->getStreet())
            ->setCompanyAddressPostalCode($address->getPostalCode())
            ->setCompanyAddressCity($address->getCity())
            ->setCompanyAddressCountry($address->getCountry());
    }

    private function addPaymentData(OrderContainer $orderContainer, OrderResponse $response)
    {
        if (
            $this->orderStateManager->isDeclined($orderContainer->getOrder())
            || $this->orderStateManager->isWaiting($orderContainer->getOrder())
        ) {
            return;
        }

        $paymentDetails = $orderContainer->getDebtorPaymentDetails();

        $response
            ->setBankAccountIban($paymentDetails->getBankAccountIban())
            ->setBankAccountBic($paymentDetails->getBankAccountBic())
        ;
    }

    private function addInvoiceData(OrderContainer $orderContainer, OrderResponse $response)
    {
        $orderPaymentDetails = $orderContainer->getPaymentDetails();
        $response
            ->setInvoiceNumber($orderContainer->getOrder()->getInvoiceNumber())
            ->setPayoutAmount($orderPaymentDetails->getPayoutAmount())
            ->setOutstandingAmount($orderPaymentDetails->getOutstandingAmount())
            ->setFeeRate($orderPaymentDetails->getFeeRate())
            ->setFeeAmount($orderPaymentDetails->getFeeAmount())
            ->setDueDate($orderPaymentDetails->getDueDate())
            ->setPendingCancellationAmount($orderPaymentDetails->getOutstandingAmountInvoiceCancellation())
            ->setPendingMerchantPaymentAmount($orderPaymentDetails->getOutstandingAmountMerchantPayment())
        ;
    }

    public function createAuthorizeResponse(OrderContainer $orderContainer): CheckoutAuthorizeOrderResponse
    {
        $order = $orderContainer->getOrder();
        $response = (new CheckoutAuthorizeOrderResponse())
            ->setState($order->getState());

        $response = $this->addReasons($orderContainer->getRiskCheckResultCollection(), $response);
        $response->setDebtorCompanySuggestion($orderContainer->getMostSimilarCandidateDTO());

        $statesAccepted = [OrderStateManager::STATE_AUTHORIZED, OrderStateManager::STATE_PRE_WAITING];
        if (in_array($order->getState(), $statesAccepted, true)) {
            if ($orderContainer->getIdentifiedDebtorCompany()->getIdentificationType() === IdentifiedDebtorCompany::IDENTIFIED_BY_COMPANY_ADDRESS) {
                $identifiedAddress = $orderContainer->getIdentifiedDebtorCompany()->getAddress();
            } else {
                $identifiedAddress = $orderContainer->getIdentifiedDebtorCompany()->getDebtorBillingAddressByUuid(
                    $orderContainer->getIdentifiedDebtorCompany()->getIdentifiedAddressUuid()
                );
            }

            $this->addCompanyData(
                $identifiedAddress,
                $orderContainer->getDebtorCompany()->getName(),
                $response
            );

            return $response;
        }

        if ($order->getMerchantDebtorId()) {
            $this->addCompanyData(
                $orderContainer->getDebtorCompany()->getAddress(),
                $orderContainer->getDebtorCompany()->getName(),
                $response
            );
        }

        return $response;
    }

    /**
     * @param  OrderResponse|CheckoutAuthorizeOrderResponse $response
     * @return OrderResponse|CheckoutAuthorizeOrderResponse $response
     */
    private function addReasons(CheckResultCollection $checkResultCollection, $response)
    {
        $failedRiskCheckResult = $checkResultCollection->getFirstHardDeclined() ?? $checkResultCollection->getFirstSoftDeclined();
        if ($failedRiskCheckResult === null) {
            return $response;
        }

        $reason = $this->declinedReasonsMapper->mapReason($failedRiskCheckResult);
        $response->setReasons([$reason]);
        $response->setDeclineReason($reason);

        return $response;
    }

    private function addDeliveryData(OrderContainer $orderContainer, OrderResponse $response): void
    {
        $response->setDeliveryAddressStreet($orderContainer->getDeliveryAddress()->getStreet())
            ->setDeliveryAddressHouseNumber($orderContainer->getDeliveryAddress()->getHouseNumber())
            ->setDeliveryAddressCity($orderContainer->getDeliveryAddress()->getCity())
            ->setDeliveryAddressPostalCode($orderContainer->getDeliveryAddress()->getPostalCode())
            ->setDeliveryAddressCountry($orderContainer->getDeliveryAddress()->getCountry());
    }

    private function addBillingAddressData(AddressEntity $billingAddress, OrderResponse $response): void
    {
        $response->setBillingAddressStreet($billingAddress->getStreet())
            ->setBillingAddressHouseNumber($billingAddress->getHouseNumber())
            ->setBillingAddressCity($billingAddress->getCity())
            ->setBillingAddressPostalCode($billingAddress->getPostalCode())
            ->setBillingAddressCountry($billingAddress->getCountry());
    }

    /**
     * @param OrderContainer $orderContainer
     * @param OrderResponse  $response
     */
    private function addExternalData(OrderContainer $orderContainer, OrderResponse $response): void
    {
        $response
            ->setDebtorExternalDataAddressCountry($orderContainer->getDebtorExternalDataAddress()->getCountry())
            ->setDebtorExternalDataAddressCity($orderContainer->getDebtorExternalDataAddress()->getCity())
            ->setDebtorExternalDataAddressPostalCode($orderContainer->getDebtorExternalDataAddress()->getPostalCode())
            ->setDebtorExternalDataAddressStreet($orderContainer->getDebtorExternalDataAddress()->getStreet())
            ->setDebtorExternalDataAddressHouse($orderContainer->getDebtorExternalDataAddress()->getHouseNumber())
            ->setDebtorExternalDataCompanyName($orderContainer->getDebtorExternalData()->getName())
            ->setDebtorExternalDataIndustrySector($orderContainer->getDebtorExternalData()->getIndustrySector())
            ->setDebtorExternalDataCustomerId($orderContainer->getDebtorExternalData()->getMerchantExternalId());
    }
}
