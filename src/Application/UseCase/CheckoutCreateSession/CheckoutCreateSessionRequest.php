<?php

namespace App\Application\UseCase\CheckoutCreateSession;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CheckoutCreateSessionRequest implements ValidatedRequestInterface
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

    public function setMerchantId(?int $merchantId): CheckoutCreateSessionRequest
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getMerchantDebtorExternalId(): ?string
    {
        return $this->merchantCustomerId;
    }

    public function setMerchantDebtorExternalId(?string $merchantDebtorExternalId): CheckoutCreateSessionRequest
    {
        $this->merchantCustomerId = $merchantDebtorExternalId;

        return $this;
    }
}
