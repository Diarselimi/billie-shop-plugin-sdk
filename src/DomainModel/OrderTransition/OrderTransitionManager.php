<?php

namespace App\DomainModel\OrderTransition;

use App\DomainModel\Order\OrderEntity;

class OrderTransitionManager
{
    /**
     * @var OrderTransitionEntity[]
     */
    private $transitions = [];
    private $transitionRepository;

    public function __construct(OrderTransitionRepositoryInterface $transitionRepository)
    {
        $this->transitionRepository = $transitionRepository;
    }

    public function registerNewTransition(OrderTransitionEntity $transition)
    {
        $this->transitions[] = $transition;
    }

    public function saveNewTransitions(OrderEntity $order)
    {
        $orderId = $order->getId();

        foreach ($this->transitions as $key => $transition) {
            if ($transition->getOrderId() === $orderId) {
                $this->transitionRepository->insert($transition);
                unset($this->transitions[$key]);
            }
        }
    }
}
