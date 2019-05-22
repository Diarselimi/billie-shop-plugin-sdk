<?php

namespace spec\App\DomainEvent\Order;

use App\Amqp\Producer\DelayedMessageProducer;
use App\DomainEvent\Order\OrderAfterStateChangeEventSubscriber;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderNotification\NotificationScheduler;
use Billie\MonitoringBundle\Service\Alerting\Sentry\Raven\RavenClient;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackClient;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackMessageFactory;
use PhpSpec\ObjectBehavior;
use Psr\Log\NullLogger;

class OrderAfterStateChangeEventSubscriberSpec extends ObjectBehavior
{
    public function let(
        OrderRepositoryInterface $orderRepository,
        NotificationScheduler $notificationScheduler,
        DelayedMessageProducer $delayedMessageProducer,
        OrderDeclinedReasonsMapper $orderDeclinedReasonsMapper,
        MerchantDebtorLimitsService $merchantDebtorLimitsService,
        RavenClient $sentry,
        SlackClient $slackClient,
        SlackMessageFactory $slackMessageFactory
    ) {
        $this->beConstructedWith(...func_get_args());

        $this
            ->setLogger(new NullLogger())
            ->setSentry($sentry)
            ->setSlackClient($slackClient)
            ->setSlackMessageFactory($slackMessageFactory)
        ;
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(OrderAfterStateChangeEventSubscriber::class);
    }
}
