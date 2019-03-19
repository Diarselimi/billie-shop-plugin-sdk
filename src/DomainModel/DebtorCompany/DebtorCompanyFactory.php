<?php

namespace App\DomainModel\DebtorCompany;

class DebtorCompanyFactory
{
    public function createFromAlfredResponse(array $data, bool $isStrictMatch = true): DebtorCompany
    {
        return (new DebtorCompany())
            ->setId($data['id'])
            ->setName($data['name'])
            ->setAddressHouse($data['address_house'])
            ->setAddressStreet($data['address_street'])
            ->setAddressPostalCode($data['address_postal_code'])
            ->setAddressCity($data['address_city'])
            ->setAddressCountry($data['address_country'])
            ->setCrefoId($data['crefo_id'])
            ->setSchufaId($data['schufa_id'])
            ->setIsBlacklisted($data['is_blacklisted'])
            ->setIsStrictMatch($isStrictMatch)
        ;
    }
}
