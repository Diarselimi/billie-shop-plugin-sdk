<?php

namespace App\DomainEvent\DebtorInformationChangeRequest;

use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntity;

class DebtorInformationChangeRequestCompletedEvent
{
    private $changeRequest;

    public function __construct(DebtorInformationChangeRequestEntity $changeRequest)
    {
        $this->changeRequest = $changeRequest;
    }

    public function getDebtorInformationChangeRequest(): DebtorInformationChangeRequestEntity
    {
        return $this->changeRequest;
    }
}
