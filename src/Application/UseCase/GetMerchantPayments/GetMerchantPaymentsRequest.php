<?php

namespace App\Application\UseCase\GetMerchantPayments;

use App\Application\UseCase\PaginationAwareInterface;
use App\Application\UseCase\PaginationAwareTrait;
use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GetMerchantPaymentsRequest implements ValidatedRequestInterface, PaginationAwareInterface
{
    use PaginationAwareTrait;

    const DEFAULT_SORTING_FIELD = 'priority';

    /**
     * @Assert\Type(type="integer")
     * @Assert\NotBlank()
     */
    private $merchantId;

    /**
     * @Assert\Uuid()
     */
    private $merchantPaymentUuid;

    /**
     * @Assert\Uuid()
     */
    private $paymentDebtorUuid;

    /**
     * @Assert\Uuid()
     */
    private $transactionUuid;

    /**
     * @Assert\Choice({"priority", "transaction_date"})
     */
    private $sortBy;

    /**
     * @Assert\Choice({"desc", "asc"})
     */
    private $sortDirection;

    /**
     * @Assert\Type(type="string")
     */
    private $searchKeyword;

    /**
     * @Assert\Uuid()
     */
    private $merchantDebtorUuid;

    /**
     * @Assert\Type(type="boolean")
     */
    private ?bool $isAllocated = null;

    /**
     * @Assert\Type(type="boolean")
     */
    private ?bool $isOverpayment = null;

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): GetMerchantPaymentsRequest
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getPaymentDebtorUuid(): ?string
    {
        return $this->paymentDebtorUuid;
    }

    public function setPaymentDebtorUuid(?string $paymentDebtorUuid): GetMerchantPaymentsRequest
    {
        $this->paymentDebtorUuid = $paymentDebtorUuid;

        return $this;
    }

    public function getTransactionUuid(): ?string
    {
        return $this->transactionUuid;
    }

    public function setTransactionUuid(?string $transactionUuid): GetMerchantPaymentsRequest
    {
        $this->transactionUuid = $transactionUuid;

        return $this;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function setSortBy(?string $sortBy): GetMerchantPaymentsRequest
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    public function getSortDirection(): ?string
    {
        return $this->sortDirection;
    }

    public function setSortDirection(?string $sortDirection): GetMerchantPaymentsRequest
    {
        $this->sortDirection = $sortDirection;

        return $this;
    }

    public function getSearchKeyword(): ?string
    {
        return $this->searchKeyword;
    }

    public function setSearchKeyword(?string $searchKeyword): GetMerchantPaymentsRequest
    {
        $this->searchKeyword = $searchKeyword;

        return $this;
    }

    public function setMerchantDebtorUuid(?string $merchantDebtorUuid): GetMerchantPaymentsRequest
    {
        $this->merchantDebtorUuid = $merchantDebtorUuid;

        return $this;
    }

    public function getMerchantDebtorUuid(): ?string
    {
        return $this->merchantDebtorUuid;
    }

    public function getMerchantPaymentUuid(): ?string
    {
        return $this->merchantPaymentUuid;
    }

    public function setMerchantPaymentUuid(?string $merchantPaymentUuid): GetMerchantPaymentsRequest
    {
        $this->merchantPaymentUuid = $merchantPaymentUuid;

        return $this;
    }

    public function isAllocated(): ?bool
    {
        return $this->isAllocated;
    }

    public function setIsAllocated(?bool $isAllocated): GetMerchantPaymentsRequest
    {
        $this->isAllocated = $isAllocated;

        return $this;
    }

    public function isOverpayment(): ?bool
    {
        return $this->isOverpayment;
    }

    public function setIsOverpayment(?bool $isOverpayment): GetMerchantPaymentsRequest
    {
        $this->isOverpayment = $isOverpayment;

        return $this;
    }
}
