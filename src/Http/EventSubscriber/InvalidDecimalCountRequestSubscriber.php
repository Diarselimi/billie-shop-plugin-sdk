<?php

namespace App\Http\EventSubscriber;

use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * This is a temporary class to check if our merchants are sending us
 * invalid decimal count in amounts like <13.629999999999995>
 */
class InvalidDecimalCountRequestSubscriber implements EventSubscriberInterface, LoggingInterface
{
    use LoggingTrait;

    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest()->request->all();
        array_walk_recursive($request, [$this, 'checkRequestProperty']);
    }

    private function checkRequestProperty($value, $key): void
    {
        if (!in_array($key, ['net', 'tax', 'gross']) || !$this->isDecimalCountInvalid($value)) {
            return;
        }

        $this->logWarning('Invalid decimal count provided for {property}: {value}', [
            'property' => $key,
            'value' => $value,
        ]);
    }

    private function isDecimalCountInvalid(string $number): bool
    {
        return strlen(substr(strrchr($number, '.'), 1)) > 2;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 9],
        ];
    }
}
