<?php

namespace App\DomainModel\OrderResponse;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorCompany\IdentifiedDebtorCompany;
use App\DomainModel\FeatureFlag\FeatureFlagManager;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderRiskCheck\CheckResultCollection;
use App\DomainModel\Payment\OrderPaymentDetailsDTO;
use App\DomainModel\Payment\PaymentsServiceInterface;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;

class OrderResponseFactory
{
    private CompaniesServiceInterface $companiesService;

    private PaymentsServiceInterface $paymentsService;

    private OrderDeclinedReasonsMapper $declinedReasonsMapper;

    private FeatureFlagManager $featureFlagManager;

    public function __construct(
        CompaniesServiceInterface $companiesService,
        PaymentsServiceInterface $paymentsService,
        OrderDeclinedReasonsMapper $declinedReasonsMapper,
        FeatureFlagManager $featureFlagManager
    ) {
        $this->companiesService = $companiesService;
        $this->paymentsService = $paymentsService;
        $this->declinedReasonsMapper = $declinedReasonsMapper;
        $this->featureFlagManager = $featureFlagManager;
    }

    public function create(OrderContainer $orderContainer): OrderResponse
    {
        $response = new OrderResponse();

        $this->addData($orderContainer, $response);
        $this->addInvoiceData($orderContainer, $response);
        $this->addLegacyInvoiceData($orderContainer, $response);

        $response->setReasons([$response->getDeclineReason()]);

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

    public function createAuthorizeResponse(OrderContainer $orderContainer): CheckoutAuthorizeOrderResponse
    {
        $order = $orderContainer->getOrder();
        $response = (new CheckoutAuthorizeOrderResponse())
            ->setState($order->getState());

        $response = $this->addReasons($orderContainer->getRiskCheckResultCollection(), $response);
        $response->setDebtorCompanySuggestion($orderContainer->getMostSimilarCandidateDTO());

        $statesAccepted = [OrderEntity::STATE_AUTHORIZED, OrderEntity::STATE_PRE_WAITING];
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

    private function addLegacyInvoiceData(OrderContainer $orderContainer, OrderResponse $response): void
    {
        if (!$this->featureFlagManager->isButlerFullyEnabled()
            && !empty($orderContainer->getOrder()->getPaymentId())
        ) {
            $orderPaymentDetails = $orderContainer->getPaymentDetails();
            $response
                ->setInvoiceNumber($orderContainer->getOrder()->getInvoiceNumber())
                ->setPayoutAmount($orderPaymentDetails->getPayoutAmount())
                ->setOutstandingAmount($orderPaymentDetails->getOutstandingAmount())
                ->setFeeRate($orderPaymentDetails->getFeeRate())
                ->setFeeAmount($orderPaymentDetails->getFeeAmount())
                ->setPendingCancellationAmount($orderPaymentDetails->getOutstandingAmountInvoiceCancellation())
                ->setPendingMerchantPaymentAmount($orderPaymentDetails->getOutstandingAmountMerchantPayment());
        } elseif (count($orderContainer->getInvoices()) >= 1) {
            $invoices = $orderContainer->getInvoices();
            /** @var Invoice $invoice */
            $invoice = array_pop($invoices);
            $amount = $invoice->getAmount()->getGross()->toFloat();
            $response
                ->setInvoiceNumber($invoice->getExternalCode())
                ->setPayoutAmount($amount)
                ->setOutstandingAmount($invoice->getOutstandingAmount()->toFloat())
                ->setFeeRate($invoice->getFeeRate()->toFloat())
                ->setFeeAmount($invoice->getFeeAmount()->getGross()->toFloat())
                ->setInvoiceNumber($invoice->getExternalCode())
                ->setPendingCancellationAmount($invoice->getInvoicePendingCancellationAmount()->getMoneyValue())
                ->setPendingMerchantPaymentAmount($invoice->getMerchantPendingPaymentAmount()->getMoneyValue());
        }
    }

    private function addInvoiceData(OrderContainer $orderContainer, OrderResponse $response): void
    {
        foreach ($orderContainer->getInvoices() as $invoice) {
            $dueDate = clone $invoice->getBillingDate();
            $dueDate->add(
                new \DateInterval(
                    sprintf('P%dD', $invoice->getDuration())
                )
            );

            $response->addInvoice(
                (new OrderInvoiceResponse())
                    ->setUuid($invoice->getUuid())
                    ->setDuration($invoice->getDuration())
                    ->setAmount($invoice->getAmount())
                    ->setDuration($invoice->getDuration())
                    ->setFeeAmount($invoice->getFeeAmount()->getGross()->toBase100())
                    ->setFeeRate($invoice->getFeeRate()->toBase100())
                    ->setInvoiceNumber($invoice->getExternalCode())
                    ->setDueDate($dueDate)
                    ->setCreatedAt($invoice->getCreatedAt())
                    ->setPayoutAmount($invoice->getPayoutAmount()->toBase100())
                    ->setState($invoice->getState())
                    ->setOutstandingAmount($invoice->getAmount()->getGross()->toBase100())
                    ->setPendingMerchantPaymentAmount($invoice->getMerchantPendingPaymentAmount()->getMoneyValue())
                    ->setPendingCancellationAmount($invoice->getInvoicePendingCancellationAmount()->getMoneyValue())
            );
        }
    }

    private function addData(OrderContainer $orderContainer, OrderResponse $response): void
    {
        $order = $orderContainer->getOrder();
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

        if ($order->isLate()) {
            $response->setDunningStatus($orderContainer->getDunningStatus());
        }

        $this->addReasons($orderContainer->getRiskCheckResultCollection(), $response);
    }

    /**
     * @param OrderContainer[]
     * @return OrderPaymentDetailsDTO[]
     */
    private function getOrderPaymentsDetails(array $orderContainers): array
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
        $financialDetails = $orderContainer->getOrderFinancialDetails();
        $createdAt = clone $orderContainer->getOrder()->getCreatedAt();
        $response
            ->setAmount(TaxedMoneyFactory::create(
                $financialDetails->getAmountGross(),
                $financialDetails->getAmountNet(),
                $financialDetails->getAmountTax()
            ))
            ->setDuration($orderContainer->getOrderFinancialDetails()->getDuration())
            ->setDueDate($createdAt->modify("+ {$financialDetails->getDuration()} days"))
            ->setUnshippedAmount(new TaxedMoney(
                $financialDetails->getUnshippedAmountGross(),
                $financialDetails->getUnshippedAmountNet(),
                $financialDetails->getUnshippedAmountTax()
            ));
    }

    private function addOrderData(OrderEntity $order, OrderResponse $response): void
    {
        $response
            ->setExternalCode($order->getExternalCode())
            ->setWorkflowName($order->getWorkflowName())
            ->setUuid($order->getUuid())
            ->setState($order->getState())
            ->setCreatedAt($order->getCreatedAt())
            ->setShippedAt($order->getShippedAt());
    }

    /**
     * @param OrderResponse|CheckoutAuthorizeOrderResponse $response
     */
    private function addCompanyData(AddressEntity $address, string $companyName, $response): void
    {
        $response
            ->setCompanyName($companyName)
            ->setCompanyAddressHouseNumber($address->getHouseNumber())
            ->setCompanyAddressStreet($address->getStreet())
            ->setCompanyAddressPostalCode($address->getPostalCode())
            ->setCompanyAddressCity($address->getCity())
            ->setCompanyAddressCountry($address->getCountry());
    }

    private function addPaymentData(OrderContainer $orderContainer, OrderResponse $response): void
    {
        if ($orderContainer->getOrder()->isDeclined() || $orderContainer->getOrder()->isWaiting()) {
            return;
        }

        $paymentDetails = $orderContainer->getDebtorPaymentDetails();

        $response
            ->setBankAccountIban($paymentDetails->getBankAccountIban())
            ->setBankAccountBic($paymentDetails->getBankAccountBic());
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
