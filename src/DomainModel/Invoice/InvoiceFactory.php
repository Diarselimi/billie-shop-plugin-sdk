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
    private UuidGeneratorInterface $uuidGenerator;

    private FeeCalculator $feeCalculator;

    public function __construct(UuidGeneratorInterface $uuidGenerator, FeeCalculator $feeCalculator)
    {
        $this->uuidGenerator = $uuidGenerator;
        $this->feeCalculator = $feeCalculator;
    }

    public function create(OrderContainer $orderContainer, TaxedMoney $amount, int $duration, string $invoiceNumber, string $proofOfDeliveryUrl = null): Invoice
    {
        $fee = $this->feeCalculator->calculate(
            $amount->getGross(),
            $duration,
            $orderContainer->getMerchantSettings()->getFeeRates()
        );

        return (new Invoice())
            ->setUuid($this->uuidGenerator->uuid4())
            ->setCustomerUuid($orderContainer->getMerchant()->getPaymentUuid())
            ->setDebtorCompanyUuid($orderContainer->getMerchantDebtor()->getCompanyUuid())
            ->setPaymentDebtorUuid($orderContainer->getMerchantDebtor()->getPaymentDebtorId())
            ->setAmount(clone $amount)
            ->setOutstandingAmount($amount->getGross())
            ->setPayoutAmount($amount->getGross()->subtract($fee->getGrossFeeAmount()))
            ->setFeeAmount(new TaxedMoney($fee->getGrossFeeAmount(), $fee->getNetFeeAmount(), $fee->getTaxFeeAmount()))
            ->setFeeRate($fee->getFeeRate())
            ->setDuration($duration)
            ->setBillingDate(new \DateTime('today'))
            ->setDueDate(new \DateTime("today + {$duration} days"))
            ->setProofOfDeliveryUrl($proofOfDeliveryUrl)
            ->setExternalCode($invoiceNumber)
        ;
    }

    public function createFromArray(array $data): Invoice
    {
        $grossAmount = new Money($data['gross_amount']);
        $netAmount = new Money($data['net_amount']);
        $taxAmount = $grossAmount->add($netAmount);

        $grossFeeAmount = new Money($data['fee_amount']);
        $netFeeAmount = new Money($data['fee_net_amount']);
        $taxFeeAmount = new Money($data['fee_vat_amount']);

        return (new Invoice())
            ->setUuid($data['uuid'])
            ->setAmount((new TaxedMoney($grossAmount, $netAmount, $taxAmount)))
            ->setOutstandingAmount(new Money($data['outstanding_amount']))
            ->setPayoutAmount(new Money($data['payout_amount']))
            ->setFeeAmount(new TaxedMoney($grossFeeAmount, $netFeeAmount, $taxFeeAmount))
            ->setFeeRate(new Percent($data['factoring_fee_rate']))
            ->setDuration($data['duration'])
            ->setBillingDate(new \DateTime($data['billing_date']))
            ->setProofOfDeliveryUrl('proof_of_delivery_url')
            ->setExternalCode($data['external_code'])
        ;
    }
}
