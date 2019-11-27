<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Order\OrderStateTransitionEntity;
use App\DomainModel\Order\OrderStateTransitionRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractStateTransitionRepository;

class OrderStateTransitionRepository extends AbstractStateTransitionRepository implements OrderStateTransitionRepositoryInterface
{
    private const TABLE_NAME = 'order_transitions';

    private const REFERENCE_FIELD_NAME = 'order_id';

    public function insert(OrderStateTransitionEntity $transition): void
    {
        $this->insertStateTransition($transition, self::TABLE_NAME, self::REFERENCE_FIELD_NAME);
    }
}
