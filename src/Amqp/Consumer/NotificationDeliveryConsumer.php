<?php

namespace App\Amqp\Consumer;

use App\Application\UseCase\NotificationDelivery\NotificationDeliveryRequest;
use App\Application\UseCase\NotificationDelivery\NotificationDeliveryUseCase;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class NotificationDeliveryConsumer implements ConsumerInterface
{
    private $useCase;

    public function __construct(NotificationDeliveryUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(AMQPMessage $msg)
    {
        $data = $msg->getBody();
        $data = json_decode($data, true);

        $request = new NotificationDeliveryRequest($data['notification_id']);
        $this->useCase->execute($request);
    }
}
