<?php

namespace App\DomainModel\Payment;

abstract class AbstractPaymentRequestDTO
{
    private $debtorPaymentId;

    private $invoiceNumber;

    private $shippedAt;

    private $duration;

    private $amountGross;

    private $externalCode;

    private $paymentUuid;

    public function getDebtorPaymentId(): ?string
    {
        return $this->debtorPaymentId;
    }

    /**
     * @param  string|null $debtorPaymentId
     * @return $this
     */
    public function setDebtorPaymentId(?string $debtorPaymentId): AbstractPaymentRequestDTO
    {
        $this->debtorPaymentId = $debtorPaymentId;

        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    /**
     * @param  string|null $invoiceNumber
     * @return $this
     */
    public function setInvoiceNumber(?string $invoiceNumber): AbstractPaymentRequestDTO
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getShippedAt(): ?\DateTime
    {
        return $this->shippedAt;
    }

    /**
     * @param  \DateTime|null $shippedAt
     * @return $this
     */
    public function setShippedAt(?\DateTime $shippedAt): AbstractPaymentRequestDTO
    {
        $this->shippedAt = $shippedAt;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    /**
     * @param  int|null $duration
     * @return $this
     */
    public function setDuration(?int $duration): AbstractPaymentRequestDTO
    {
        $this->duration = $duration;

        return $this;
    }

    public function getAmountGross(): ?float
    {
        return $this->amountGross;
    }

    /**
     * @param  float|null $amountGross
     * @return $this
     */
    public function setAmountGross(?float $amountGross): AbstractPaymentRequestDTO
    {
        $this->amountGross = $amountGross;

        return $this;
    }

    public function getExternalCode(): ?string
    {
        return $this->externalCode;
    }

    /**
     * @param  string|null $externalCode
     * @return $this
     */
    public function setExternalCode(?string $externalCode): AbstractPaymentRequestDTO
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getPaymentUuid(): ?string
    {
        return $this->paymentUuid;
    }

    /**
     * @param  string|null $paymentUuid
     * @return $this
     */
    public function setPaymentUuid(?string $paymentUuid): AbstractPaymentRequestDTO
    {
        $this->paymentUuid = $paymentUuid;

        return $this;
    }

    abstract public function toArray(): array;
}
