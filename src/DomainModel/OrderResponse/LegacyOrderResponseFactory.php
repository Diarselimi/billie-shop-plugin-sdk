<?php

namespace App\DomainModel\OrderResponse;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorCompany\IdentifiedDebtorCompany;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderRiskCheck\CheckResultCollection;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;

class LegacyOrderResponseFactory
{
    private CompaniesServiceInterface $companiesService;

    private OrderDeclinedReasonsMapper $declinedReasonsMapper;

    public function __construct(
        CompaniesServiceInterface $companiesService,
        OrderDeclinedReasonsMapper $declinedReasonsMapper
    ) {
        $this->companiesService = $companiesService;
        $this->declinedReasonsMapper = $declinedReasonsMapper;
    }

    public function create(OrderContainer $orderContainer): LegacyOrderResponse
    {
        $response = new LegacyOrderResponse();

        $this->addData($orderContainer, $response);
        $this->addLegacyInvoiceData($orderContainer, $response);
        $this->addInvoiceData($orderContainer, $response); // for dashboard

        $response->setReasons([$response->getDeclineReason()]);

        return $response;
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
            $identifiedAddress = $this->getIdentifiedAddress($orderContainer);
            if ($identifiedAddress instanceof AddressEntity) {
                $this->addCompanyData(
                    $identifiedAddress,
                    $orderContainer->getDebtorCompany()->getName(),
                    $response
                );
            }

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

    private function getIdentifiedAddress(OrderContainer $orderContainer): ?AddressEntity
    {
        $identifiedCompany = $orderContainer->getIdentifiedDebtorCompany();

        if (!($identifiedCompany instanceof IdentifiedDebtorCompany)) {
            return null;
        }

        $identifiedAddress = null;

        if ($identifiedCompany->getIdentificationType() === IdentifiedDebtorCompany::IDENTIFIED_BY_COMPANY_ADDRESS) {
            $identifiedAddress = $identifiedCompany->getAddress();
        } elseif ($identifiedCompany->getIdentifiedAddressUuid() !== null) {
            $identifiedAddress = $identifiedCompany->getDebtorBillingAddressByUuid(
                $identifiedCompany->getIdentifiedAddressUuid()
            );
        }

        return $identifiedAddress;
    }

    private function addLegacyInvoiceData(OrderContainer $orderContainer, LegacyOrderResponse $response): void
    {
        if ($orderContainer->getInvoices()->isEmpty()) {
            return;
        }

        $invoice = $orderContainer->getInvoices()->getFirst();
        $response
            ->setInvoiceNumber($invoice->getExternalCode())
            ->setPayoutAmount($invoice->getPayoutAmount()->getMoneyValue())
            ->setOutstandingAmount($invoice->getOutstandingAmount()->toFloat())
            ->setFeeRate($invoice->getFeeRate()->toFloat())
            ->setFeeAmount($invoice->getFeeAmount()->getGross()->toFloat())
            ->setInvoiceNumber($invoice->getExternalCode())
            ->setPendingCancellationAmount($invoice->getInvoicePendingCancellationAmount()->getMoneyValue())
            ->setPendingMerchantPaymentAmount($invoice->getMerchantPendingPaymentAmount()->getMoneyValue());
    }

    private function addInvoiceData(OrderContainer $orderContainer, LegacyOrderResponse $response): void
    {
        if ($orderContainer->getOrder()->isWorkflowV1()) {
            return;
        }

        /** @var Invoice $invoice */
        foreach ($orderContainer->getInvoices() as $invoice) {
            $dueDate = clone $invoice->getBillingDate();
            $dueDate->add(
                new \DateInterval(
                    sprintf('P%dD', $invoice->getDuration())
                )
            );

            $response->addInvoice(
                (new LegacyOrderInvoiceResponse())
                    ->setUuid($invoice->getUuid())
                    ->setDuration($invoice->getDuration())
                    ->setAmount($invoice->getAmount())
                    ->setDuration($invoice->getDuration())
                    ->setFeeAmount($invoice->getFeeAmount()->getGross()->getMoneyValue())
                    ->setFeeRate($invoice->getFeeRate()->toBase100())
                    ->setInvoiceNumber($invoice->getExternalCode())
                    ->setDueDate($dueDate)
                    ->setCreatedAt($invoice->getCreatedAt())
                    ->setPayoutAmount($invoice->getPayoutAmount()->getMoneyValue())
                    ->setState($invoice->getState())
                    ->setOutstandingAmount($invoice->getOutstandingAmount()->getMoneyValue())
                    ->setPendingMerchantPaymentAmount($invoice->getMerchantPendingPaymentAmount()->getMoneyValue())
                    ->setPendingCancellationAmount($invoice->getInvoicePendingCancellationAmount()->getMoneyValue())
            );
        }
    }

    private function addData(OrderContainer $orderContainer, LegacyOrderResponse $response): void
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
            $response->setDunningStatus($orderContainer->getDunningState());
        }

        $this->addReasons($orderContainer->getRiskCheckResultCollection(), $response);

        $response->setPaymentMethods($orderContainer->getPaymentMethods());
    }

    /**
     * @param OrderContainer[]
     * @return DebtorCompany[]
     */
    private function getDebtorCompanies(array $orderContainers): array
    {
        $debtorIds = array_map(
            static function (OrderContainer $orderContainer) {
                if ($orderContainer->getOrder()->getMerchantDebtorId() !== null) {
                    return $orderContainer->getMerchantDebtor()->getDebtorId();
                }

                return null;
            },
            $orderContainers
        );
        $debtorIds = array_filter($debtorIds);

        return $this->companiesService->getDebtors($debtorIds);
    }

    private function addFinancialDetails(OrderContainer $orderContainer, LegacyOrderResponse $response): void
    {
        $financialDetails = $orderContainer->getOrderFinancialDetails();

        $clonedInvoiceCollection = clone $orderContainer->getInvoices();
        if ($orderContainer->getOrder()->isCanceled() && !$clonedInvoiceCollection->isEmpty()) {
            $clonedInvoiceCollection->getLastInvoice()->getCreditNotes()->pop();
        }

        $calculatedTaxedAmount = TaxedMoneyFactory::create(
            $financialDetails->getAmountGross()->subtract(
                $gross = $clonedInvoiceCollection->getInvoicesCreditNotesGrossSum()
            ),
            $financialDetails->getAmountNet()->subtract(
                $net = $clonedInvoiceCollection->getInvoicesCreditNotesNetSum()
            ),
            $financialDetails->getAmountTax()->subtract($gross->subtract($net))
        );

        $createdAt = new \DateTime();
        $createdAt->setTimestamp($orderContainer->getOrder()->getCreatedAt()->getTimestamp());
        $response
            ->setAmount($calculatedTaxedAmount)
            ->setDuration($orderContainer->getOrderFinancialDetails()->getDuration())
            ->setDueDate($createdAt->modify("+ {$financialDetails->getDuration()} days"))
            ->setUnshippedAmount(
                new TaxedMoney(
                    $financialDetails->getUnshippedAmountGross(),
                    $financialDetails->getUnshippedAmountNet(),
                    $financialDetails->getUnshippedAmountTax()
                )
            );

        if (!$orderContainer->getInvoices()->isEmpty()) {
            $outstandingAmount = $orderContainer
                ->getInvoices()
                ->getLastInvoice()
                ->getOutstandingAmount()
                ->getMoneyValue();

            $response
                ->setOutstandingAmount($outstandingAmount);
        }
    }

    private function addOrderData(OrderEntity $order, LegacyOrderResponse $response): void
    {
        $response
            ->setExternalCode($order->getExternalCode())
            ->setWorkflowName($order->getWorkflowName())
            ->setUuid($order->getUuid())
            ->setState($order->getState())
            ->setCreatedAt($order->getCreatedAt())
            ->setShippedAt($order->getShippedAt())
            ->setWorkflowName($order->getWorkflowName());
    }

    /**
     * @param LegacyOrderResponse|CheckoutAuthorizeOrderResponse $response
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

    private function addPaymentData(OrderContainer $orderContainer, LegacyOrderResponse $response): void
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
     * @param  LegacyOrderResponse|CheckoutAuthorizeOrderResponse $response
     * @return LegacyOrderResponse|CheckoutAuthorizeOrderResponse $response
     */
    private function addReasons(CheckResultCollection $checkResultCollection, $response)
    {
        $failedRiskCheckResult = $checkResultCollection->getFirstHardDeclined(
            ) ?? $checkResultCollection->getFirstSoftDeclined();
        if ($failedRiskCheckResult === null) {
            return $response;
        }

        $reason = $this->declinedReasonsMapper->mapReason($failedRiskCheckResult);
        $response->setDeclineReason($reason);

        return $response;
    }

    private function addDeliveryData(OrderContainer $orderContainer, LegacyOrderResponse $response): void
    {
        $response->setDeliveryAddressStreet($orderContainer->getDeliveryAddress()->getStreet())
            ->setDeliveryAddressHouseNumber($orderContainer->getDeliveryAddress()->getHouseNumber())
            ->setDeliveryAddressCity($orderContainer->getDeliveryAddress()->getCity())
            ->setDeliveryAddressPostalCode($orderContainer->getDeliveryAddress()->getPostalCode())
            ->setDeliveryAddressCountry($orderContainer->getDeliveryAddress()->getCountry());
    }

    private function addBillingAddressData(AddressEntity $billingAddress, LegacyOrderResponse $response): void
    {
        $response->setBillingAddressStreet($billingAddress->getStreet())
            ->setBillingAddressHouseNumber($billingAddress->getHouseNumber())
            ->setBillingAddressCity($billingAddress->getCity())
            ->setBillingAddressPostalCode($billingAddress->getPostalCode())
            ->setBillingAddressCountry($billingAddress->getCountry());
    }

    private function addExternalData(OrderContainer $orderContainer, LegacyOrderResponse $response): void
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
