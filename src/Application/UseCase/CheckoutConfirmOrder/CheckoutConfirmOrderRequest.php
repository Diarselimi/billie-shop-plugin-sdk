<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutConfirmOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as PaellaAssert;
use App\DomainModel\DebtorCompany\DebtorCompanyRequest;
use OpenApi\Annotations as OA;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(schema="CheckoutConfirmOrderRequest", required={"amount", "duration", "debtor_company"}, properties={
 *      @OA\Property(property="amount", ref="#/components/schemas/AmountDTO"),
 *      @OA\Property(property="duration", ref="#/components/schemas/OrderDuration"),
 *      @OA\Property(property="debtor_company_name", ref="#/components/schemas/TinyText", description="Company name"),
 *      @OA\Property(property="debtor_company_address", ref="#/components/schemas/Address"),
 *      @OA\Property(property="delivery_address", ref="#/components/schemas/Address", nullable=true),
 *      @OA\Property(property="external_code", ref="#/components/schemas/TinyText", description="Order external code", example="DE123456-1")
 * })
 */
class CheckoutConfirmOrderRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\Length(min=1, max=255, allowEmptyString=false, minMessage="The order id cannot be an empty string.")
     * @Assert\Type(type="string")
     * @PaellaAssert\OrderExternalCode
     */
    private $orderId;

    private $merchantId;

    private $sessionUuid;

    /**
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $amount;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     */
    private $duration;

    /**
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $debtorCompanyRequest;

    /**
     * @Assert\Valid()
     */
    private $deliveryAddress;

    public function getAmount(): TaxedMoney
    {
        return $this->amount;
    }

    public function setAmount(TaxedMoney $amount): CheckoutConfirmOrderRequest
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

    public function getSessionUuid(): string
    {
        return $this->sessionUuid;
    }

    public function setSessionUuid(string $sessionUuid): CheckoutConfirmOrderRequest
    {
        $this->sessionUuid = $sessionUuid;

        return $this;
    }

    public function getDebtorCompanyRequest(): DebtorCompanyRequest
    {
        return $this->debtorCompanyRequest;
    }

    public function setDebtorCompanyRequest(?DebtorCompanyRequest $debtorCompanyRequest): CheckoutConfirmOrderRequest
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

    public function getExternalCode(): ?string
    {
        return $this->orderId;
    }

    public function setExternalCode($orderId): CheckoutConfirmOrderRequest
    {
        $this->orderId = $orderId;

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
}
