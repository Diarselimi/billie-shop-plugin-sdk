<?php

namespace App\DomainModel\DebtorExternalData;

interface DebtorExternalDataRepositoryInterface
{
    public function insert(DebtorExternalDataEntity $debtor): void;

    public function getOneByIdRaw(int $id): ? array;

    public function getOneById(int $id): ? DebtorExternalDataEntity;
}
