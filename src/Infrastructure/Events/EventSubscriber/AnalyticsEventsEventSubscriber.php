<?php

declare(strict_types=1);

namespace App\Infrastructure\Events\EventSubscriber;

use App\Application\UseCase\CheckoutConfirmOrder\AnalyticsEvent\SessionConfirmationExecutedAnalyticsEvent;
use App\DomainEvent\AnalyticsEvent\AbstractAnalyticsEvent;
use App\Infrastructure\Events\Repository\EventRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AnalyticsEventsEventSubscriber implements EventSubscriberInterface
{
    private $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function getSubscribedEvents()
    {
        return [
            SessionConfirmationExecutedAnalyticsEvent::class => 'registerEvent',
            // and many more
        ];
    }

    public function registerEvent(AbstractAnalyticsEvent $event)
    {
        $this->repository->add($event);
    }
}
