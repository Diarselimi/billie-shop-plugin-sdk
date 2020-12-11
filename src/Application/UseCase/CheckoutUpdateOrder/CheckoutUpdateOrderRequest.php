<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutUpdateOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(schema="CheckoutUpdateOrderRequest", required={"billing_address"}, properties={
 *      @OA\Property(property="billing_address", ref="#/components/schemas/CreateOrderAddressRequest"),
 *      @OA\Property(property="duration", ref="#/components/schemas/OrderDuration"),
 * })
 */
class CheckoutUpdateOrderRequest implements ValidatedRequestInterface
{
    private string $sessionUuid;

    /**
     * @Assert\Valid()
     */
    private ?CreateOrderAddressRequest $billingAddress = null;

    /**
     * @Assert\Choice(choices={null, 30, 45, 60, 90, 120}, message="Duration is not valid.")
     */
    private ?int $duration = null;

    public function getSessionUuid(): string
    {
        return $this->sessionUuid;
    }

    public function setSessionUuid(string $sessionUuid): CheckoutUpdateOrderRequest
    {
        $this->sessionUuid = $sessionUuid;

        return $this;
    }

    public function getBillingAddress(): ?CreateOrderAddressRequest
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?CreateOrderAddressRequest $billingAddress): CheckoutUpdateOrderRequest
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): CheckoutUpdateOrderRequest
    {
        $this->duration = $duration;

        return $this;
    }
}
