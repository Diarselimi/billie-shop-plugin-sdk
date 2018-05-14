<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\OrderTransition\OrderTransitionEntity;
use App\DomainModel\OrderTransition\OrderTransitionRepositoryInterface;

class OrderTransitionRepository extends AbstractRepository implements OrderTransitionRepositoryInterface
{
    public function insert(OrderTransitionEntity $transition): void
    {
        $id = $this->doInsert('
            INSERT INTO order_transitions
            (order_id, transition, transited_at)
            VALUES
            (:order_id, :transition, :transited_at)
            
        ', [
            'order_id' => $transition->getOrderId(),
            'transition' => $transition->getTransition(),
            'transited_at' => $transition->getTransitedAt()->format('Y-m-d H:i:s'),
        ]);

        $transition->setId($id);
    }
}
