<?php

namespace App\DomainModel\OrderResponse;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderStateManager;

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
        $response = (new OrderResponse())
            ->setExternalCode($order->getExternalCode())
            ->setUuid($order->getUuid())
            ->setState($order->getState())
            ->setAmountGross($orderContainer->getOrderFinancialDetails()->getAmountGross())
            ->setAmountNet($orderContainer->getOrderFinancialDetails()->getAmountNet())
            ->setAmountTax($orderContainer->getOrderFinancialDetails()->getAmountTax())
            ->setDebtorExternalDataAddressCountry($orderContainer->getDebtorExternalDataAddress()->getCountry())
            ->setDebtorExternalDataAddressCity($orderContainer->getDebtorExternalDataAddress()->getCity())
            ->setDebtorExternalDataAddressPostalCode($orderContainer->getDebtorExternalDataAddress()->getPostalCode())
            ->setDebtorExternalDataAddressStreet($orderContainer->getDebtorExternalDataAddress()->getStreet())
            ->setDebtorExternalDataAddressHouse($orderContainer->getDebtorExternalDataAddress()->getHouseNumber())
            ->setDebtorExternalDataCompanyName($orderContainer->getDebtorExternalData()->getName())
            ->setDebtorExternalDataIndustrySector($orderContainer->getDebtorExternalData()->getIndustrySector())
            ->setCreatedAt($order->getCreatedAt())
            ->setDebtorExternalDataCustomerId($orderContainer->getDebtorExternalData()->getMerchantExternalId())
            ->setDuration($orderContainer->getOrderFinancialDetails()->getDuration())
            ->setShippedAt($order->getShippedAt())
        ;

        $this->addDeliveryData($orderContainer, $response);

        $this->addBillingAddressData($orderContainer->getBillingAddress(), $response);

        if ($order->getMerchantDebtorId()) {
            $this->addCompanyData($orderContainer, $response);
            $this->addPaymentData($orderContainer, $response);
        }

        if ($this->orderStateManager->wasShipped($order) || $this->orderStateManager->isComplete($order)) {
            $this->addInvoiceData($order, $response);
        }

        if ($this->orderStateManager->isLate($order)) {
            $response->setDunningStatus($orderContainer->getDunningStatus());
        }

        $response = $this->addReasons($order, $response);

        return $response;
    }

    private function addCompanyData(OrderContainer $orderContainer, OrderResponse $response)
    {
        $debtor = $orderContainer->getDebtorCompany();

        $response
            ->setCompanyName($debtor->getName())
            ->setCompanyAddressHouseNumber($debtor->getAddressHouse())
            ->setCompanyAddressStreet($debtor->getAddressStreet())
            ->setCompanyAddressPostalCode($debtor->getAddressPostalCode())
            ->setCompanyAddressCity($debtor->getAddressCity())
            ->setCompanyAddressCountry($debtor->getAddressCountry())
        ;
    }

    private function addPaymentData(OrderContainer $orderContainer, OrderResponse $response)
    {
        if (
            $this->orderStateManager->isDeclined($orderContainer->getOrder())
            || $this->orderStateManager->isWaiting($orderContainer->getOrder())
        ) {
            return;
        }

        $paymentDetails = $this->paymentsService->getDebtorPaymentDetails($orderContainer->getMerchantDebtor()->getPaymentDebtorId());

        $response
            ->setBankAccountIban($paymentDetails->getBankAccountIban())
            ->setBankAccountBic($paymentDetails->getBankAccountBic())
        ;
    }

    private function addInvoiceData(OrderEntity $order, OrderResponse $response)
    {
        $orderPaymentDetails = $this->paymentsService->getOrderPaymentDetails($order->getPaymentId());
        $response
            ->setInvoiceNumber($order->getInvoiceNumber())
            ->setPayoutAmount($orderPaymentDetails->getPayoutAmount())
            ->setOutstandingAmount($orderPaymentDetails->getOutstandingAmount())
            ->setFeeRate($orderPaymentDetails->getFeeRate())
            ->setFeeAmount($orderPaymentDetails->getFeeAmount())
            ->setDueDate($orderPaymentDetails->getDueDate())
        ;
    }

    public function createAuthorizeResponse(OrderContainer $orderContainer): CheckoutSessionAuthorizeResponse
    {
        $order = $orderContainer->getOrder();
        $response = new CheckoutSessionAuthorizeResponse();

        $response = $this->addReasons($order, $response);

        return $response;
    }

    /**
     * @param  OrderResponse|CheckoutSessionAuthorizeResponse $response
     * @return OrderResponse|CheckoutSessionAuthorizeResponse $response
     */
    private function addReasons(OrderEntity $order, $response)
    {
        if (!method_exists($response, 'setReasons')) {
            return $response;
        }

        if ($this->orderStateManager->isDeclined($order) || $this->orderStateManager->isWaiting($order)) {
            $response->setReasons($this->declinedReasonsMapper->mapReasons($order));
        }

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
}
