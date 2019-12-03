<?php

namespace spec\App\DomainModel\Order;

use App\Application\Exception\WorkflowException;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsException;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\CreateOrderCrossChecksService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use Billie\MonitoringBundle\Service\Alerting\Sentry\Raven\RavenClient;
use PhpSpec\ObjectBehavior;
use Psr\Log\NullLogger;

class CreateOrderCrossChecksServiceSpec extends ObjectBehavior
{
    private const ORDER_ID = 77;

    private const AMOUNT = 560;

    public function let(
        MerchantDebtorLimitsService $merchantDebtorLimitsService,
        MerchantRepositoryInterface $merchantRepository,
        OrderContainer $orderContainer,
        OrderEntity $order,
        OrderFinancialDetailsEntity $orderFinancialDetails,
        MerchantEntity $merchant,
        RavenClient $sentry
    ) {
        $orderContainer->getMerchant()->willReturn($merchant);
        $orderContainer->getOrder()->willReturn($order);
        $orderContainer->getOrderFinancialDetails()->willReturn($orderFinancialDetails);
        $order->getId()->willReturn(self::ORDER_ID);
        $orderFinancialDetails->getAmountGross()->willReturn(self::AMOUNT);

        $this->beConstructedWith(...func_get_args());

        $this->setLogger(new NullLogger())->setSentry($sentry);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CreateOrderCrossChecksService::class);
    }

    public function it_throws_exception_on_merchant_debtor_limit_lock_failure(
        MerchantDebtorLimitsService $merchantDebtorLimitsService,
        OrderContainer $orderContainer,
        MerchantEntity $merchant
    ) {
        $merchantDebtorLimitsService->lock($orderContainer)->shouldBeCalledOnce()->willThrow(MerchantDebtorLimitsException::class);
        $merchant->reduceFinancingLimit(self::AMOUNT)->shouldNotBeCalled();

        $this->shouldThrow(WorkflowException::class)->during('run', [$orderContainer]);
    }

    public function it_throws_exception_on_merchant_limit_lock_failure(
        MerchantDebtorLimitsService $merchantDebtorLimitsService,
        MerchantRepositoryInterface $merchantRepository,
        OrderContainer $orderContainer,
        MerchantEntity $merchant
    ) {
        $merchantDebtorLimitsService->lock($orderContainer)->shouldBeCalledOnce();
        $merchant->reduceFinancingLimit(self::AMOUNT)->shouldBeCalledOnce()->willThrow(MerchantDebtorLimitsException::class);

        $merchantRepository->update($merchant)->shouldNotBeCalled();

        $this->shouldThrow(WorkflowException::class)->during('run', [$orderContainer]);
    }

    public function it_locks_the_limit(
        MerchantDebtorLimitsService $merchantDebtorLimitsService,
        MerchantRepositoryInterface $merchantRepository,
        OrderContainer $orderContainer,
        MerchantEntity $merchant
    ) {
        $merchantDebtorLimitsService->lock($orderContainer)->shouldBeCalledOnce();
        $merchant->reduceFinancingLimit(self::AMOUNT)->shouldBeCalledOnce();

        $merchantRepository->update($merchant)->shouldBeCalledOnce();

        $this->run($orderContainer);
    }
}
