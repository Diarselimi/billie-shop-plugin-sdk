<?php

namespace App\DomainModel\SignatoryPower;

use App\Support\AbstractFactory;

class SignatoryPowerDTOFactory extends AbstractFactory
{
    public function createFromArray(array $data): SignatoryPowerDTO
    {
        return (new SignatoryPowerDTO())
            ->setAutomaticallyIdentifiedAsUser(false)
            ->setUuid($data['uuid'])
            ->setFirstName($data['first_name'])
            ->setLastName($data['last_name'])
            ->setEmail($data['email'])
            ->setAdditionalSignatoriesRequired($data['additional_signatories_required'] ?? 0)
            ->setAddressHouse($data['address_house'])
            ->setAddressStreet($data['address_street'])
            ->setAddressCity($data['address_city'])
            ->setAddressPostalCode($data['address_postal_code'])
            ->setAddressCountry($data['address_country'])
            ->setCompanyUuid($data['company_uuid'])
            ->setIdentityVerificationUrl($data['identity_verification_url'])
            ->setIsIdentityVerified($data['is_identity_verified'] ?? false)
            ;
    }
}
