<?php

namespace App\Infrastructure\Alfred;

use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Alfred\DebtorDTO;

class Alfred implements AlfredInterface
{
    public function getDebtor(int $debtorId): DebtorDTO
    {
        return (new DebtorDTO())
            ->setName('Dabredo UG')
            ->setAddressHouse('52')
            ->setAddressStreet('Charlotte')
            ->setAddressPostalCode('10999')
            ->setAddressCity('Berlin')
            ->setAddressCountry('Germany')
        ;
    }

    public function identifyDebtor(array $debtorData): DebtorDTO
    {
    }
}
