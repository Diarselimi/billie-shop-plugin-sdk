<?php

namespace App\DomainModel\GetSignatoryPowers;

use App\Support\AbstractFactory;

class SignatoryPowerDTOFactory extends AbstractFactory
{
    public function createFromArray(array $row): GetSignatoryPowerDTO
    {
        return (new GetSignatoryPowerDTO())
            ->setUuid($row['uuid'])
            ->setFirstName($row['first_name'])
            ->setLastName($row['last_name'])
            ->setAdditionalSignatoriesRequired($row['additional_signatories_required'])
            ->setAddressHouse($row['address_house'])
            ->setAddressStreet($row['address_street'])
            ->setAddressCity($row['address_city'])
            ->setAddressPostalCode($row['address_postal_code'])
            ->setAddressCountry($row['address_country'])
            ->setAutomaticallyIdentifiedAsUser(false)
            ;
    }
}
