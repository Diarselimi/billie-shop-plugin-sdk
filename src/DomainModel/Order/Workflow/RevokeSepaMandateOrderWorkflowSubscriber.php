<?php

namespace App\DomainModel\Order\Workflow;

use App\DomainModel\Order\OrderEntity;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Sepa\Client\DomainModel\SepaClientInterface;
use Ozean12\Support\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class RevokeSepaMandateOrderWorkflowSubscriber implements EventSubscriberInterface, LoggingInterface
{
    use LoggingTrait;

    private SepaClientInterface $sepaClient;

    public function __construct(SepaClientInterface $sepaClient)
    {
        $this->sepaClient = $sepaClient;
    }

    public function onCancel(Event $event): void
    {
        /** @var OrderEntity $order */
        $order = $event->getSubject();

        if ($order->getDebtorSepaMandateUuid() === null) {
            return;
        }

        try {
            $this->sepaClient->revokeMandate($order->getDebtorSepaMandateUuid());
        } catch (HttpExceptionInterface $exception) {
            $this->logSuppressedException(
                $exception,
                sprintf('Mandate revoke call failed for uuid %s ', $order->getDebtorSepaMandateUuid())
            );
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.order_v1.entered.canceled' => 'onCancel',
            'workflow.order_v2.entered.canceled' => 'onCancel',
        ];
    }
}
