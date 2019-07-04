<?php

namespace App\Application\UseCase\UpdateMerchantDebtorWhitelist;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class WhitelistMerchantDebtorRequest implements ValidatedRequestInterface
{
    private $debtorUuid;

    /**
     * @var bool|mixed
     * @Assert\NotBlank()
     */
    private $isWhitelisted;

    public function __construct(string $debtorUuid, bool $isWhitelisted)
    {
        $this->debtorUuid = $debtorUuid;
        $this->isWhitelisted = $isWhitelisted;
    }

    public function getDebtorUuid(): string
    {
        return $this->debtorUuid;
    }

    public function setDebtorUuid(string $debtorUuid): WhitelistMerchantDebtorRequest
    {
        $this->debtorUuid = $debtorUuid;

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
}
