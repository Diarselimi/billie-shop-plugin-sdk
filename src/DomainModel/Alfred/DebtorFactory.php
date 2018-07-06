<?php

namespace App\DomainModel\Alfred;

class DebtorFactory
{
    public function createFromAlfredResponse(array $data)
    {
        return (new DebtorDTO())
            ->setId($data['id'])
            ->setName($data['name'])
            ->setAddressHouse($data['address_house'])
            ->setAddressStreet($data['address_street'])
            ->setAddressPostalCode($data['address_postal_code'])
            ->setAddressCity($data['address_city'])
            ->setAddressCountry($data['address_country'])
            ->setPaymentId($data['payment_id'])
            ->setCrefoId($data['crefo_id'])
            ->setSchufaId($data['schufa_id'])
            ->setIsBlacklisted($data['is_blacklisted'])
        ;
    }
}
