<?php

declare(strict_types=1);

namespace App\Amqp\Handler;

use App\DomainModel\Order\DomainEvent\OrderInWaitingStateDomainEvent;
use App\DomainModel\Order\OrderEntity;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class OrderInWaitingStateHandler extends AbstractOrderDeclineByStateHandler implements MessageHandlerInterface
{
    public function __invoke(OrderInWaitingStateDomainEvent $message)
    {
        $this->execute($message, [OrderEntity::STATE_WAITING]);
    }
}
