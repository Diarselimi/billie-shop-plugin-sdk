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
 * })
 */
class CheckoutUpdateOrderRequest implements ValidatedRequestInterface
{
    private $sessionUuid;

    /**
     * @Assert\NotBlank()
     * @Assert\Valid()
     * @var CreateOrderAddressRequest
     */
    private $billingAddress;

    public function getSessionUuid(): string
    {
        return $this->sessionUuid;
    }

    public function setSessionUuid(string $sessionUuid): CheckoutUpdateOrderRequest
    {
        $this->sessionUuid = $sessionUuid;

        return $this;
    }

    public function getBillingAddress(): CreateOrderAddressRequest
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?CreateOrderAddressRequest $billingAddress): CheckoutUpdateOrderRequest
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }
}
