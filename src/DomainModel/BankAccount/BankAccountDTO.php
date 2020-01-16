<?php

declare(strict_types=1);

namespace App\DomainModel\BankAccount;

class BankAccountDTO
{
    private $uuid;

    private $name;

    private $bankName;

    private $iban;

    private $bic;

    private $paymentUuid;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): BankAccountDTO
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): BankAccountDTO
    {
        $this->name = $name;

        return $this;
    }

    public function getIban(): IbanDTO
    {
        return $this->iban;
    }

    public function setIban(IbanDTO $iban): BankAccountDTO
    {
        $this->iban = $iban;

        return $this;
    }

    public function getBic(): string
    {
        return $this->bic;
    }

    public function setBic(string $bic): BankAccountDTO
    {
        $this->bic = $bic;

        return $this;
    }

    public function getPaymentUuid(): string
    {
        return $this->paymentUuid;
    }

    public function setPaymentUuid(string $paymentUuid): BankAccountDTO
    {
        $this->paymentUuid = $paymentUuid;

        return $this;
    }

    public function getBankName(): string
    {
        return $this->bankName;
    }

    public function setBankName(string $bankName): BankAccountDTO
    {
        $this->bankName = $bankName;

        return $this;
    }
}
