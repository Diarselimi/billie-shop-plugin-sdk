<?php

namespace App\DomainModel\SignatoryPowersSelection;

use App\DomainModel\GetSignatoryPowers\GetSignatoryPowerDTO;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\Helper\String\StringSearch;

class UserSignatoryPowerMatcher
{
    private $stringSearch;

    public function __construct(StringSearch $stringSearch)
    {
        $this->stringSearch = $stringSearch;
    }

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
            $isIdentifiedAsUser = $this->stringSearch->areAllWordsInString(
                [$merchantUser->getFirstName(), $merchantUser->getLastName()],
                "{$signatoryPowersDTO->getFirstName()} {$signatoryPowersDTO->getLastName()}"
            );

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
