<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestTransitionEntity;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestTransitionRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractStateTransitionRepository;

class DebtorInformationChangeRequestTransitionRepository extends AbstractStateTransitionRepository implements DebtorInformationChangeRequestTransitionRepositoryInterface
{
    public const TABLE_NAME = 'debtor_information_change_request_transitions';

    public function insert(DebtorInformationChangeRequestTransitionEntity $entity): void
    {
        $this->insertStateTransition($entity, self::TABLE_NAME, 'debtor_information_change_request_id');
    }
}
