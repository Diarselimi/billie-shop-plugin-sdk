<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutConfirmOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as CustomConstrains;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use App\DomainModel\DebtorCompany\DebtorCompanyRequest;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(schema="CheckoutConfirmOrderRequest", required={"amount", "duration", "debtor_company"}, properties={
 *      @OA\Property(property="amount", ref="#/components/schemas/AmountDTO"),
 *      @OA\Property(property="duration", ref="#/components/schemas/OrderDuration"),
 *      @OA\Property(property="debtor_company", ref="#/components/schemas/DebtorCompanyRequest"),
 *      @OA\Property(property="delivery_address", ref="#/components/schemas/CreateOrderAddressRequest", nullable=true)
 * })
 */
class CheckoutConfirmOrderRequest implements ValidatedRequestInterface
{
    private $sessionUuid;

    /**
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $amount;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @CustomConstrains\OrderDuration()
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
}
