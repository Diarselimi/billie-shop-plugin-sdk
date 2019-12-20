<?php

declare(strict_types=1);

namespace App\DomainModel\BankAccount;

class IbanDTO
{
    private $iban;

    private $country;

    private $bankCode;

    private $account;

    public function getIban(): string
    {
        return $this->iban;
    }

    public function setIban(string $iban): IbanDTO
    {
        $this->iban = $iban;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): IbanDTO
    {
        $this->country = $country;

        return $this;
    }

    public function getBankCode(): string
    {
        return $this->bankCode;
    }

    public function setBankCode(string $bankCode): IbanDTO
    {
        $this->bankCode = $bankCode;

        return $this;
    }

    public function getAccount(): string
    {
        return $this->account;
    }

    public function setAccount(string $account): IbanDTO
    {
        $this->account = $account;

        return $this;
    }
}
