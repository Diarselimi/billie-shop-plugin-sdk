<?php

namespace App\DomainModel\Borscht;

class DebtorPaymentDetailsDTO
{
    private $bankAccountIban;
    private $bankAccountBic;

    public function getBankAccountIban()
    {
        return $this->bankAccountIban;
    }

    public function setBankAccountIban($bankAccountIban)
    {
        $this->bankAccountIban = $bankAccountIban;

        return $this;
    }

    public function getBankAccountBic()
    {
        return $this->bankAccountBic;
    }

    public function setBankAccountBic($bankAccountBic)
    {
        $this->bankAccountBic = $bankAccountBic;

        return $this;
    }
}
