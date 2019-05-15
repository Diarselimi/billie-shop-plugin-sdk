<?php

namespace spec\App\Application\UseCase\CreateOrder;

use App\Application\UseCase\CreateOrder\CreateOrderUseCase;
use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\MerchantDebtor\DebtorFinder;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use Billie\MonitoringBundle\Service\Alerting\Sentry\Raven\RavenClient;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PhpSpec\ObjectBehavior;
use Psr\Log\NullLogger;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateOrderUseCaseSpec extends ObjectBehavior
{
    public function let(
        OrderPersistenceService $orderPersistenceService,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderRepositoryInterface $orderRepository,
        DebtorFinder $debtorFinderService,
        ValidatorInterface $validator,
        ProducerInterface $producer,
        OrderResponseFactory $orderResponseFactory,
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository,
        OrderStateManager $orderStateManager,
        RavenClient $sentry
    ) {
        $this->beConstructedWith(...func_get_args());

        $this->setLogger(new NullLogger())->setSentry($sentry);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CreateOrderUseCase::class);
    }
}
