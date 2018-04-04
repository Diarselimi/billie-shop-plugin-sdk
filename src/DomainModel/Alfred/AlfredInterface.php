<?php

namespace App\DomainModel\Alfred;

interface AlfredInterface
{
    public function getDebtor(int $debtorId): DebtorDTO;
    public function identifyDebtor(array $debtorData): DebtorDTO;
}
