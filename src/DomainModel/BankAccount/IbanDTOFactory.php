<?php

declare(strict_types=1);

namespace App\DomainModel\BankAccount;

class IbanDTOFactory
{
    public function createFromString(string $iban): IbanDTO
    {
        $ibanObject = new \IBAN($iban);

        if (!$ibanObject->Verify()) {
            throw new InvalidIbanException();
        }

        return (new IbanDTO())
            ->setIban($ibanObject->MachineFormat())
            ->setCountry($ibanObject->Country())
            ->setBankCode($ibanObject->Bank())
            ->setAccount($ibanObject->Account())
            ;
    }
}
