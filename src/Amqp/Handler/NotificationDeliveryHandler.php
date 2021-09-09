<?php

namespace App\Amqp\Handler;

use App\Application\UseCase\NotificationDelivery\NotificationDeliveryRequest;
use App\Application\UseCase\NotificationDelivery\NotificationDeliveryUseCase;
use App\DomainModel\Order\DomainEvent\NotificationDeliveryDomainEvent;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NotificationDeliveryHandler implements MessageHandlerInterface
{
    private $useCase;

    public function __construct(NotificationDeliveryUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(NotificationDeliveryDomainEvent $message)
    {
        $request = new NotificationDeliveryRequest($message->getNotificationId());
        $this->useCase->execute($request);
    }
}
