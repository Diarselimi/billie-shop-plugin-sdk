<?php

declare(strict_types=1);

namespace App\Amqp\Handler;

use App\DomainModel\Order\DomainEvent\OrderInPreWaitingStateDomainEvent;
use App\DomainModel\Order\OrderEntity;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class OrderInPreWaitingStateHandler extends AbstractOrderDeclineByStateHandler implements MessageHandlerInterface
{
    public function __invoke(OrderInPreWaitingStateDomainEvent $message)
    {
        $this->execute($message, [OrderEntity::STATE_PRE_WAITING]);
    }
}
