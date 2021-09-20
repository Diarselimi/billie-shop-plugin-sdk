<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\DomainEvent\OrderRiskCheck\RiskCheckResultEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderRiskCheckResultEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            RiskCheckResultEvent::class => 'onRiskCheckResult',
        ];
    }

    public function onRiskCheckResult(RiskCheckResultEvent $event)
    {
        $riskCheckResultCollection = $event->getOrderContainer()->getRiskCheckResultCollection();
        $riskCheckResultCollection->add($event->getResult());
    }
}
