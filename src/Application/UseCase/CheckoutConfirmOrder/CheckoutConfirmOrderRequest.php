<?php

namespace App\Application\UseCase\CheckoutConfirmOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAmountRequest;
use App\Application\UseCase\DebtorCompanyRequest;
use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as CustomConstrains;
use Symfony\Component\Validator\Constraints as Assert;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="CheckoutConfirmOrderRequest", required={"amount", "duration", "debtor_company"}, properties={
 *      @OA\Property(property="amount", ref="#/components/schemas/CreateOrderAmountRequest"),
 *      @OA\Property(property="duration", ref="#/components/schemas/OrderDuration"),
 *      @OA\Property(property="debtor_company", ref="#/components/schemas/DebtorCompanyRequest")
 * })
 */
class CheckoutConfirmOrderRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\Valid()
     */
    private $amount;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="int")
     * @CustomConstrains\OrderDuration()
     */
    private $duration;

    private $sessionUuid;

    /**
     * @Assert\Valid()
     */
    private $debtorCompanyRequest;

    public function getAmount(): ?CreateOrderAmountRequest
    {
        return $this->amount;
    }

    public function setAmount(?CreateOrderAmountRequest $amount): CheckoutConfirmOrderRequest
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): CheckoutConfirmOrderRequest
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

    public function setDebtorCompanyRequest(DebtorCompanyRequest $debtorCompanyRequest): CheckoutConfirmOrderRequest
    {
        $this->debtorCompanyRequest = $debtorCompanyRequest;

        return $this;
    }
}
