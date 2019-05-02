<?php

namespace App\Application\UseCase\UpdateMerchantDebtorWhitelist;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class WhitelistMerchantDebtorRequest implements ValidatedRequestInterface
{
    private $merchantExternalId;

    /**
     * @var bool|mixed
     * @Assert\NotBlank()
     */
    private $isWhitelisted;

    private $merchantId;

    public function __construct(string $merchantExternalId, int $merchantId, bool $isWhitelisted)
    {
        $this->merchantExternalId = $merchantExternalId;
        $this->merchantId = $merchantId;
        $this->isWhitelisted = $isWhitelisted;
    }

    public function getMerchantDebtorExternalId(): string
    {
        return $this->merchantExternalId;
    }

    public function setMerchantDebtorExternalId(string $merchantExternalId): WhitelistMerchantDebtorRequest
    {
        $this->merchantExternalId = $merchantExternalId;

        return $this;
    }

    public function getIsWhitelisted(): bool
    {
        return $this->isWhitelisted;
    }

    public function setIsWhitelisted(bool $isWhitelisted): WhitelistMerchantDebtorRequest
    {
        $this->isWhitelisted = $isWhitelisted;

        return $this;
    }

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }
}
