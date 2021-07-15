<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Application\UseCase\CheckoutConfirmOrder\AnalyticsEvent\SessionConfirmationExecutedAnalyticsEvent;
use App\Infrastructure\Events\Repository\EventRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class KernelTerminateEventSubscriber implements EventSubscriberInterface
{
    private $repository;

    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.terminate' => 'flushHttpLogs',
        ];
    }

    public function flushHttpLogs(TerminateEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $route = $request->get('_route');

        if (in_array($route, ['public_oa_checkout_session_confirm', 'oa_checkout_session_confirm'], true)) {
            $identifierId = $request->get('sessionUuid');
            $this->repository->add(
                (new SessionConfirmationExecutedAnalyticsEvent($identifierId))
                    ->setRequest($request->getContent())
                    ->setResponse($response->getContent())
                    ->setStatusCode($response->getStatusCode())
            );
        }

        $this->repository->flush();
    }
}
