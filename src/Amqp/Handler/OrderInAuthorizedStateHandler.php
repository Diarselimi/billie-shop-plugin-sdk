<?php

declare(strict_types=1);

namespace App\Amqp\Handler;

use App\DomainModel\Order\DomainEvent\OrderInAuthorizedStateDomainEvent;
use App\DomainModel\Order\OrderEntity;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class OrderInAuthorizedStateHandler extends AbstractOrderDeclineByStateHandler implements MessageHandlerInterface
{
    public function __invoke(OrderInAuthorizedStateDomainEvent $message)
    {
        $this->execute($message, [OrderEntity::STATE_AUTHORIZED]);
    }
}
