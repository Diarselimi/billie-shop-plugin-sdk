<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetSignatoryPowerDetails;

use App\DomainModel\SignatoryPower\SignatoryPowerDTO;

class GetSignatoryPowerDetailsRequest
{
    private $merchantName;

    private $signatoryPowerDTO;

    public function getMerchantName(): string
    {
        return $this->merchantName;
    }

    public function setMerchantName(string $merchantName): GetSignatoryPowerDetailsRequest
    {
        $this->merchantName = $merchantName;

        return $this;
    }

    public function getSignatoryPowerDTO(): SignatoryPowerDTO
    {
        return $this->signatoryPowerDTO;
    }

    public function setSignatoryPowerDTO(SignatoryPowerDTO $signatoryPowerDTO): GetSignatoryPowerDetailsRequest
    {
        $this->signatoryPowerDTO = $signatoryPowerDTO;

        return $this;
    }
}
