<?php

declare(strict_types=1);

namespace App\DomainModel\ExternalDebtorResponse;

use App\Support\AbstractFactory;

class ExternalDebtorFactory extends AbstractFactory
{
    public function createFromArray(array $data): ExternalDebtorDTO
    {
        return (new ExternalDebtorDTO())
            ->setName($data['name'])
            ->setLegalForm($data['legal_form'])
            ->setAddressStreet($data['street_name'])
            ->setAddressCity($data['city'])
            ->setAddressPostalCode($data['postal_code'])
            ->setAddressCountry($data['country'])
            ->setAddressHouseNumber($data['house_number'])
            ;
    }
}
