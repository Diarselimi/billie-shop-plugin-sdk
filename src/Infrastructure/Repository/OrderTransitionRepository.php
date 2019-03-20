<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\OrderTransition\OrderTransitionEntity;
use App\DomainModel\OrderTransition\OrderTransitionRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class OrderTransitionRepository extends AbstractPdoRepository implements OrderTransitionRepositoryInterface
{
    public function insert(OrderTransitionEntity $transition): void
    {
        $id = $this->doInsert('
            INSERT INTO order_transitions
            (order_id, `from`, `to`, transition, transited_at)
            VALUES
            (:order_id, :from, :to, :transition, :transited_at)
            
        ', [
            'order_id' => $transition->getOrderId(),
            'from' => $transition->getFrom(),
            'to' => $transition->getTo(),
            'transition' => $transition->getTransition(),
            'transited_at' => $transition->getTransitedAt()->format(self::DATE_FORMAT),
        ]);

        $transition->setId($id);
    }
}
