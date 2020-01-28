<?php

declare(strict_types=1);

namespace App\Application\UseCase\StartIdentityVerificationBySignatoryPower;

use App\Application\UseCase\StartIdentityVerificationRedirectsTrait;
use App\Application\UseCase\ValidatedRequestInterface;
use App\DomainModel\SignatoryPower\SignatoryPowerDTO;

class StartIdentityVerificationBySignatoryPowerRequest implements ValidatedRequestInterface
{
    use StartIdentityVerificationRedirectsTrait;

    private $merchantId;

    private $signatoryPowerDTO;

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): StartIdentityVerificationBySignatoryPowerRequest
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getSignatoryPowerDTO(): SignatoryPowerDTO
    {
        return $this->signatoryPowerDTO;
    }

    public function setSignatoryPowerDTO(SignatoryPowerDTO $signatoryPowerDTO): StartIdentityVerificationBySignatoryPowerRequest
    {
        $this->signatoryPowerDTO = $signatoryPowerDTO;

        return $this;
    }
}
