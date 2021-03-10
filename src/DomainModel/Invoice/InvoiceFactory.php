<?php

namespace App\DomainModel\Invoice;

use App\DomainModel\Fee\FeeCalculator;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\Helper\Uuid\UuidGeneratorInterface;
use App\Support\AbstractFactory;
use Ozean12\Money\Money;
use Ozean12\Money\Percent;
use Ozean12\Money\TaxedMoney\TaxedMoney;

class InvoiceFactory extends AbstractFactory
{
    private const INITIAL_INVOICE_STATE = 'new';

    private UuidGeneratorInterface $uuidGenerator;

    private FeeCalculator $feeCalculator;

    public function __construct(UuidGeneratorInterface $uuidGenerator, FeeCalculator $feeCalculator)
    {
        $this->uuidGenerator = $uuidGenerator;
        $this->feeCalculator = $feeCalculator;
    }

    public function create(
        OrderContainer $orderContainer,
        TaxedMoney $amount,
        int $duration,
        string $invoiceNumber,
        string $proofOfDeliveryUrl = null
    ): Invoice {
        $fee = $this->feeCalculator->calculate(
            $amount->getGross(),
            $duration,
            $orderContainer->getMerchantSettings()->getFeeRates()
        );

        $invoiceAndPaymentUuid = $this->uuidGenerator->uuid4();

        return (new Invoice())
            ->setUuid($invoiceAndPaymentUuid)
            ->setCustomerUuid($orderContainer->getMerchant()->getPaymentUuid())
            ->setDebtorCompanyUuid($orderContainer->getMerchantDebtor()->getCompanyUuid())
            ->setPaymentDebtorUuid($orderContainer->getMerchantDebtor()->getPaymentDebtorId())
            ->setPaymentUuid($invoiceAndPaymentUuid)
            ->setAmount(clone $amount)
            ->setOutstandingAmount($amount->getGross())
            ->setPayoutAmount($amount->getGross()->subtract($fee->getGrossFeeAmount()))
            ->setFeeAmount(new TaxedMoney($fee->getGrossFeeAmount(), $fee->getNetFeeAmount(), $fee->getTaxFeeAmount()))
            ->setFeeRate($fee->getFeeRate())
            ->setDuration($duration)
            ->setState(self::INITIAL_INVOICE_STATE)
            ->setBillingDate(new \DateTime('today'))
            ->setDueDate(new \DateTime("today + {$duration} days"))
            ->setCreatedAt(new \DateTime())
            ->setProofOfDeliveryUrl($proofOfDeliveryUrl)
            ->setExternalCode($invoiceNumber)
            ->setInvoicePendingCancellationAmount(new Money(0))
            ->setMerchantPendingPaymentAmount(new Money(0))
        ;
    }

    public function createFromArray(array $data): Invoice
    {
        $grossAmount = new Money($data['amount'], 0);
        $netAmount = new Money($data['amount_net'], 0);
        $taxAmount = new Money($data['amount_tax'], 0);

        $grossFeeAmount = new Money($data['fee_amount'], 0);
        $netFeeAmount = new Money($data['fee_amount_net'], 0);
        $taxFeeAmount = new Money($data['fee_amount_vat'], 0);

        return (new Invoice())
            ->setUuid($data['uuid'])
            ->setPaymentUuid($data['payment_uuid'])
            ->setAmount((new TaxedMoney($grossAmount, $netAmount, $taxAmount)))
            ->setOutstandingAmount(new Money($data['outstanding_amount'], 0))
            ->setPayoutAmount(new Money($data['payout_amount'], 0))
            ->setFeeAmount(new TaxedMoney($grossFeeAmount, $netFeeAmount, $taxFeeAmount))
            ->setFeeRate(new Percent($data['factoring_fee_rate'], 0))
            ->setDuration($data['duration'])
            ->setCreatedAt(new \DateTime($data['created_at']))
            ->setDueDate(new \DateTime($data['due_date']))
            ->setDuration($data['duration'])
            ->setBillingDate(new \DateTime($data['billing_date']))
            ->setProofOfDeliveryUrl('proof_of_delivery_url')
            ->setExternalCode($data['external_code'])
            ->setState($data['state'])
            ->setCreatedAt(new \DateTime($data['created_at']))
            ->setMerchantPendingPaymentAmount(new Money($data['merchant_pending_payment_amount'], 0))
            ->setInvoicePendingCancellationAmount(new Money($data['invoice_pending_cancellation_amount'], 0));
    }
}
