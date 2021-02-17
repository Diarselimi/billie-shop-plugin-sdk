<?php

namespace App\DomainModel\Invoice;

use Ozean12\Money\Money;
use Ozean12\Money\Percent;
use Ozean12\Money\TaxedMoney\TaxedMoney;

class Invoice
{
    private string $uuid;

    private TaxedMoney $amount;

    private TaxedMoney $feeAmount;

    private Money $outstandingAmount;

    private Money $payoutAmount;

    private string $debtorCompanyUuid;

    private string $customerUuid;

    private string $paymentDebtorUuid;

    private string $paymentUuid;

    private Percent $feeRate;

    private int $duration;

    private \DateTime $dueDate;

    private \DateTime $billingDate;

    private \DateTime $createdAt;

    private ?string $proofOfDeliveryUrl = null;

    private string $externalCode;

    private string $state;

    private Money $invoicePendingCancellationAmount;

    private Money $merchantPendingPaymentAmount;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): Invoice
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getAmount(): TaxedMoney
    {
        return $this->amount;
    }

    public function setAmount(TaxedMoney $amount): Invoice
    {
        $this->amount = $amount;

        return $this;
    }

    public function getFeeAmount(): TaxedMoney
    {
        return $this->feeAmount;
    }

    public function setFeeAmount(TaxedMoney $feeAmount): Invoice
    {
        $this->feeAmount = $feeAmount;

        return $this;
    }

    public function getOutstandingAmount(): Money
    {
        return $this->outstandingAmount;
    }

    public function setOutstandingAmount(Money $outstandingAmount): Invoice
    {
        $this->outstandingAmount = $outstandingAmount;

        return $this;
    }

    public function getPayoutAmount(): Money
    {
        return $this->payoutAmount;
    }

    public function setPayoutAmount(Money $payoutAmount): Invoice
    {
        $this->payoutAmount = $payoutAmount;

        return $this;
    }

    public function getDebtorCompanyUuid(): string
    {
        return $this->debtorCompanyUuid;
    }

    public function setDebtorCompanyUuid(string $debtorCompanyUuid): Invoice
    {
        $this->debtorCompanyUuid = $debtorCompanyUuid;

        return $this;
    }

    public function getCustomerUuid(): string
    {
        return $this->customerUuid;
    }

    public function setCustomerUuid(string $customerUuid): Invoice
    {
        $this->customerUuid = $customerUuid;

        return $this;
    }

    public function getPaymentDebtorUuid(): string
    {
        return $this->paymentDebtorUuid;
    }

    public function setPaymentDebtorUuid(string $paymentDebtorUuid): Invoice
    {
        $this->paymentDebtorUuid = $paymentDebtorUuid;

        return $this;
    }

    public function getFeeRate(): Percent
    {
        return $this->feeRate;
    }

    public function setFeeRate(Percent $feeRate): Invoice
    {
        $this->feeRate = $feeRate;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): Invoice
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDueDate(): \DateTime
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTime $dueDate): Invoice
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getBillingDate(): \DateTime
    {
        return $this->billingDate;
    }

    public function setBillingDate(\DateTime $billingDate): Invoice
    {
        $this->billingDate = $billingDate;

        return $this;
    }

    public function getProofOfDeliveryUrl(): ?string
    {
        return $this->proofOfDeliveryUrl;
    }

    public function setProofOfDeliveryUrl(?string $proofOfDeliveryUrl = null): Invoice
    {
        $this->proofOfDeliveryUrl = $proofOfDeliveryUrl;

        return $this;
    }

    public function getExternalCode(): string
    {
        return $this->externalCode;
    }

    public function setExternalCode(string $externalCode): Invoice
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getPaymentUuid(): string
    {
        return $this->paymentUuid;
    }

    public function setPaymentUuid(string $paymentUuid): self
    {
        $this->paymentUuid = $paymentUuid;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): Invoice
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): Invoice
    {
        $this->state = $state;

        return $this;
    }

    public function getInvoicePendingCancellationAmount(): Money
    {
        return $this->invoicePendingCancellationAmount;
    }

    public function setInvoicePendingCancellationAmount(Money $invoicePendingCancellationAmount): Invoice
    {
        $this->invoicePendingCancellationAmount = $invoicePendingCancellationAmount;

        return $this;
    }

    public function getMerchantPendingPaymentAmount(): Money
    {
        return $this->merchantPendingPaymentAmount;
    }

    public function setMerchantPendingPaymentAmount(Money $merchantPendingPaymentAmount): Invoice
    {
        $this->merchantPendingPaymentAmount = $merchantPendingPaymentAmount;

        return $this;
    }
}
