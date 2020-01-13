<?php

namespace App\DomainModel\SignatoryPowersSelection;

use App\DomainModel\GetSignatoryPowers\GetSignatoryPowerDTO;
use App\DomainModel\MerchantUser\MerchantUserEntity;

class UserSignatoryPowerMatcher
{
    public function identify(MerchantUserEntity $merchantUser, GetSignatoryPowerDTO ...$signatoryPowerDTOs)
    {
        if ($merchantUser->getSignatoryPowerUuid()) {
            $this->identifyByUuid($merchantUser->getSignatoryPowerUuid(), ...$signatoryPowerDTOs);
        } else {
            $this->identifyByFullName($merchantUser, ...$signatoryPowerDTOs);
        }
    }

    private function identifyByFullName(MerchantUserEntity $merchantUser, GetSignatoryPowerDTO ...$signatoryPowerDTOs): void
    {
        foreach ($signatoryPowerDTOs as $signatoryPowersDTO) {
            $isIdentifiedAsUser = mb_strtolower($merchantUser->getFirstName()) === mb_strtolower($signatoryPowersDTO->getFirstName())
                && mb_strtolower($merchantUser->getLastName()) === mb_strtolower($signatoryPowersDTO->getLastName());

            if ($isIdentifiedAsUser) {
                $signatoryPowersDTO->setAutomaticallyIdentifiedAsUser($isIdentifiedAsUser);

                break;
            }
        }
    }

    private function identifyByUuid(string $signatoryPowerUuid, GetSignatoryPowerDTO ...$signatoryPowerDTOs): void
    {
        foreach ($signatoryPowerDTOs as $signatoryPowerDTO) {
            if ($signatoryPowerDTO->getUuid() === $signatoryPowerUuid) {
                $signatoryPowerDTO->setAutomaticallyIdentifiedAsUser(true);

                break;
            }
        }
    }
}
