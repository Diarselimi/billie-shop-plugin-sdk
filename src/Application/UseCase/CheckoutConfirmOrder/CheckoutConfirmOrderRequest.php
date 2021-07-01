<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutConfirmOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as PaellaAssert;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Validator\Constraints as Assert;

class CheckoutConfirmOrderRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\Length(min=1, max=255, allowEmptyString=false, minMessage="The order id cannot be an empty string.")
     * @Assert\Type(type="string")
     * @PaellaAssert\OrderExternalCode
     */
    private ?string $externalCode;

    private int $merchantId;

    private string $sessionUuid;

    /**
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private ?TaxedMoney $amount;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     */
    private ?int $duration;

    /**
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $debtorCompanyRequest;

    /**
     * @Assert\Valid()
     */
    private ?CreateOrderAddressRequest $deliveryAddress;

    public function getExternalCode(): ?string
    {
        return $this->externalCode;
    }

    public function setExternalCode(?string $externalCode): CheckoutConfirmOrderRequest
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): CheckoutConfirmOrderRequest
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getSessionUuid(): string
    {
        return $this->sessionUuid;
    }

    public function setSessionUuid(string $sessionUuid): CheckoutConfirmOrderRequest
    {
        $this->sessionUuid = $sessionUuid;

        return $this;
    }

    public function getAmount(): ?TaxedMoney
    {
        return $this->amount;
    }

    public function setAmount(?TaxedMoney $amount): CheckoutConfirmOrderRequest
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): CheckoutConfirmOrderRequest
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDebtorCompanyRequest()
    {
        return $this->debtorCompanyRequest;
    }

    public function setDebtorCompanyRequest($debtorCompanyRequest): CheckoutConfirmOrderRequest
    {
        $this->debtorCompanyRequest = $debtorCompanyRequest;

        return $this;
    }

    public function getDeliveryAddress(): ?CreateOrderAddressRequest
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?CreateOrderAddressRequest $deliveryAddress): CheckoutConfirmOrderRequest
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }
}
