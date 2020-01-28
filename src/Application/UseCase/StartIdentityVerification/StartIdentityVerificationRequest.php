<?php

declare(strict_types=1);

namespace App\Application\UseCase\StartIdentityVerification;

use App\Application\UseCase\StartIdentityVerificationRedirectsTrait;
use App\Application\UseCase\ValidatedRequestInterface;

class StartIdentityVerificationRequest implements ValidatedRequestInterface
{
    use StartIdentityVerificationRedirectsTrait;

    private $merchantId;

    private $merchantPaymentUuid;

    private $merchantUserId;

    private $signatoryPowerUuid;

    private $email;

    private $firstName;

    private $lastName;

    public function __construct(int $merchantId, string $merchantPaymentUuid)
    {
        $this->merchantId = $merchantId;
        $this->merchantPaymentUuid = $merchantPaymentUuid;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getMerchantPaymentUuid(): string
    {
        return $this->merchantPaymentUuid;
    }

    public function getMerchantUserId(): int
    {
        return $this->merchantUserId;
    }

    public function getSignatoryPowerUuid(): ?string
    {
        return $this->signatoryPowerUuid;
    }

    public function setSignatoryPowerUuid(string $signatoryPowerUuid): StartIdentityVerificationRequest
    {
        $this->signatoryPowerUuid = $signatoryPowerUuid;

        return $this;
    }

    public function setMerchantUserId(int $merchantUserId): StartIdentityVerificationRequest
    {
        $this->merchantUserId = $merchantUserId;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): StartIdentityVerificationRequest
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): StartIdentityVerificationRequest
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): StartIdentityVerificationRequest
    {
        $this->lastName = $lastName;

        return $this;
    }
}
