<?php

namespace spec\App\DomainModel\Order;

use App\DomainModel\Order\CreateOrderCrossChecksService;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use Billie\MonitoringBundle\Service\Alerting\Sentry\Raven\RavenClient;
use PhpSpec\ObjectBehavior;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\Workflow;

class OrderStateManagerSpec extends ObjectBehavior
{
    public function let(
        OrderRepositoryInterface $orderRepository,
        Workflow $workflow,
        CreateOrderCrossChecksService $approveCrossChecksService,
        EventDispatcherInterface $eventDispatcher,
        OrderChecksRunnerService $checksRunnerService,
        RavenClient $sentry
    ) {
        $this->beConstructedWith(...func_get_args());

        $this->setLogger(new NullLogger())->setSentry($sentry);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(OrderStateManager::class);
    }
}
