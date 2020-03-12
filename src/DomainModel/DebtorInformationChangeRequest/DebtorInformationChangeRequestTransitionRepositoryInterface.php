<?php

namespace App\DomainModel\DebtorInformationChangeRequest;

interface DebtorInformationChangeRequestTransitionRepositoryInterface
{
    public function insert(DebtorInformationChangeRequestTransitionEntity $entity): void;
}
