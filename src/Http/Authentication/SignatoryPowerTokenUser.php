<?php

declare(strict_types=1);

namespace App\Http\Authentication;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\SignatoryPower\SignatoryPowerDTO;

class SignatoryPowerTokenUser extends AbstractUser
{
    private const AUTH_ROLE = 'ROLE_AUTHENTICATED_AS_SIGNATORY_POWER_TOKEN_USER';

    private $signatoryPowerDTO;

    public function __construct(MerchantEntity $merchant, SignatoryPowerDTO $signatoryPowerDTO)
    {
        $this->signatoryPowerDTO = $signatoryPowerDTO;
        parent::__construct($merchant);
    }

    public function getSignatoryPowerDTO(): SignatoryPowerDTO
    {
        return $this->signatoryPowerDTO;
    }

    public function getRoles(): array
    {
        return [self::AUTH_ROLE];
    }
}
