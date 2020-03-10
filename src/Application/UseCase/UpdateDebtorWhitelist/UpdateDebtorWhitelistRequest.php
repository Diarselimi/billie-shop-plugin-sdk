<?php

namespace App\Application\UseCase\UpdateDebtorWhitelist;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateDebtorWhitelistRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\Uuid()
     * @Assert\NotBlank()
     */
    private $companyUuid;

    /**
     * @Assert\NotNull()
     * @Assert\Type(type="bool")
     */
    private $isWhitelisted;

    public function __construct(string $companyUuid, $isWhitelisted)
    {
        $this->companyUuid = $companyUuid;
        $this->isWhitelisted = $isWhitelisted;
    }

    public function getCompanyUuid(): string
    {
        return $this->companyUuid;
    }

    public function setCompanyUuid(string $companyUuid): UpdateDebtorWhitelistRequest
    {
        $this->companyUuid = $companyUuid;

        return $this;
    }

    public function isWhitelisted(): bool
    {
        return $this->isWhitelisted;
    }

    public function setIsWhitelisted(bool $isWhitelisted): UpdateDebtorWhitelistRequest
    {
        $this->isWhitelisted = $isWhitelisted;

        return $this;
    }
}
