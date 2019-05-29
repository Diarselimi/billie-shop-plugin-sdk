<?php

namespace App\Application\UseCase\CheckoutSession;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CreateCheckoutSessionRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     */
    private $merchantCustomerId;

    /**
     * @Assert\NotBlank()
     * @Assert\GreaterThan(0)
     */
    private $merchantId;

    public function getMerchantId(): ?int
    {
        return $this->merchantId;
    }

    public function setMerchantId(?int $merchantId): CreateCheckoutSessionRequest
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getMerchantDebtorExternalId(): ?string
    {
        return $this->merchantCustomerId;
    }

    public function setMerchantDebtorExternalId(?string $merchantDebtorExternalId): CreateCheckoutSessionRequest
    {
        $this->merchantCustomerId = $merchantDebtorExternalId;

        return $this;
    }
}
