<?php

namespace spec\App\Application\UseCase\CreateOrder;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\Application\UseCase\CreateOrder\CreateOrderUseCase;
use App\Application\UseCase\CreateOrder\Request\CreateOrderAmountRequest;
use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\MerchantDebtor\DebtorFinder;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use Billie\MonitoringBundle\Service\Alerting\Sentry\Raven\RavenClient;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\NullLogger;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateOrderUseCaseSpec extends ObjectBehavior
{
    private $orderPersistenceService;

    private $checkoutSessionRepository;

    private $orderChecksRunnerService;

    private $orderStateManager;

    private $debtorFinderService;

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
        CheckoutSessionRepositoryInterface $checkoutSessionRepository
    ) {
        $this->orderPersistenceService = $orderPersistenceService;
        $this->checkoutSessionRepository = $checkoutSessionRepository;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->orderStateManager = $orderStateManager;
        $this->debtorFinderService = $debtorFinderService;

        $this->beConstructedWith(...func_get_args());
        $validator->validate(Argument::any(), Argument::any(), Argument::any())->willReturn(new ConstraintViolationList());
        $debtorFinderService->findDebtor(Argument::any(), Argument::any())->willReturn(null);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CreateOrderUseCase::class);
    }

    public function it_should_be_declined_if_some_pre_identification_check_fail()
    {
        $request = $this->createMockRequest();

        $orderContainer = (new OrderContainer())
            ->setOrder(
                (new OrderEntity())
                ->setCheckoutSessionId($request->getCheckoutSessionId())
            );

        $this->orderPersistenceService->persistFromRequest($request)->willReturn($orderContainer);
        $this->orderChecksRunnerService->runPreIdentificationChecks($orderContainer)->willReturn(false);
        $this->checkoutSessionRepository->invalidateById($request->getCheckoutSessionId())->shouldBeCalledOnce();
        $this->orderStateManager->decline($orderContainer)->shouldBeCalledOnce();

        $this->shouldNotThrow(\Exception::class)->during('execute', [$request]);
    }

    public function it_should_be_declined_if_post_identifications_checks_fail()
    {
        $request = $this->createMockRequest();

        $orderContainer = (new OrderContainer())
            ->setMerchantSettings(
                (new MerchantSettingsEntity())
                ->setMerchantId(1)
                ->setUseExperimentalDebtorIdentification(false)
            )
            ->setOrder(
                (new OrderEntity())
                    ->setCheckoutSessionId($request->getCheckoutSessionId())
            );

        $this->orderPersistenceService->persistFromRequest($request)->willReturn($orderContainer);
        $this->orderChecksRunnerService->runPreIdentificationChecks(Argument::any())->willReturn(true);
        $this->orderChecksRunnerService->runPostIdentificationChecks(Argument::any())->willReturn(false);

        $this->checkoutSessionRepository->invalidateById($request->getCheckoutSessionId())->shouldBeCalledOnce();
        $this->orderStateManager->decline(Argument::any())->shouldBeCalledOnce();

        $this->shouldNotThrow(\Exception::class)->during('execute', [$request]);
    }

    private function createMockRequest(): CreateOrderRequest
    {
        return (new CreateOrderRequest())
            ->setDuration(30)
            ->setAmount(
                (new CreateOrderAmountRequest())
                    ->setNet(123.3)
                    ->setTax(123.33)
                    ->setGross(22.22)
            )
            ->setMerchantId(1)
            ->setCheckoutSessionId(1)
            ->setExternalCode('aaa123')
            ->setComment('test');
    }
}
