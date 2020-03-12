<?php

namespace App\DomainModel\DebtorInformationChangeRequest;

interface DebtorInformationChangeRequestRepositoryInterface
{
    public function insert(DebtorInformationChangeRequestEntity $debtorInformationChangeRequestEntity): void;

    public function update(DebtorInformationChangeRequestEntity $entity): void;
}
