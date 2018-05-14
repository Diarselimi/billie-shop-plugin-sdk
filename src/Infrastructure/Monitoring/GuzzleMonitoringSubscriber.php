<?php

namespace App\Infrastructure\Monitoring;

use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\Http\HttpConstantsInterface;
use EightPoints\Bundle\GuzzleBundle\Events\GuzzleEvents;
use EightPoints\Bundle\GuzzleBundle\Events\PostTransactionEvent;
use EightPoints\Bundle\GuzzleBundle\Events\PreTransactionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

class GuzzleMonitoringSubscriber implements EventSubscriberInterface, LoggingInterface
{
    use LoggingTrait;

    private $ridProvider;

    public function __construct(RidProvider $ridProvider)
    {
        $this->ridProvider = $ridProvider;
    }

    public function onPreTransaction(PreTransactionEvent $event)
    {
        $transaction = $event->getTransaction();
        $transaction = $transaction->withAddedHeader(HttpConstantsInterface::REQUEST_HEADER_RID, $this->ridProvider->getRid());

        $event->setTransaction($transaction);
    }

    public function onPostTransaction(PostTransactionEvent $event)
    {
        $transaction = $event->getTransaction();
        $responseIsSuccessful = in_array(
            $transaction->getStatusCode(),
            [Response::HTTP_OK, Response::HTTP_CREATED, Response::HTTP_NO_CONTENT],
            true
        );

        if (!$responseIsSuccessful) {
            $this->logError("Guzzle service {$event->getServiceName()} exception", [
                'code' => $transaction->getStatusCode(),
                'body' => (string) $transaction->getBody(),
            ]);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            GuzzleEvents::PRE_TRANSACTION => 'onPreTransaction',
            GuzzleEvents::POST_TRANSACTION => 'onPostTransaction',
        ];
    }
}
