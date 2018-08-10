<?php

namespace App\Amqp\EventSubscriber;

use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use OldSound\RabbitMqBundle\Event\BeforeProcessingMessageEvent;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BeforeProcessingSubscriber implements EventSubscriberInterface, LoggingInterface
{
    use LoggingTrait;

    public function beforeProcessing(BeforeProcessingMessageEvent $event)
    {
        $message = $event->getAMQPMessage();

        $this->logEvent($message);
        $this->preventDeadLoop($message);
    }

    public static function getSubscribedEvents()
    {
        return [BeforeProcessingMessageEvent::NAME => 'beforeProcessing'];
    }

    private function logEvent(AMQPMessage $message)
    {
        $this->logInfo('Queue message received', [
            'body' => $message->getBody(),
            'queue' => $message->get('routing_key'),
        ]);
    }

    private function preventDeadLoop(AMQPMessage $message)
    {
        return; // disabled

        $message->get('channel')->basic_ack($message->get('delivery_tag'));
    }
}
