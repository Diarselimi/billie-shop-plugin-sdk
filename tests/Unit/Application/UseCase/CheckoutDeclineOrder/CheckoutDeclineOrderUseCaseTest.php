<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\CheckoutDeclineOrder;

use App\Application\Tracking\TrackingEventCollector;
use App\Application\UseCase\CheckoutDeclineOrder\CheckoutDeclineOrderRequest;
use App\Application\UseCase\CheckoutDeclineOrder\CheckoutDeclineOrderUseCase;
use App\DomainModel\Address\AddressEntity;
use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Order\Lifecycle\DeclineOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\Tests\Unit\UnitTestCase;
use Ozean12\Sepa\Client\DomainModel\SepaClientInterface;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;

class CheckoutDeclineOrderUseCaseTest extends UnitTestCase
{
    private ObjectProphecy $workFlowRegistry;

    private ObjectProphecy $declineOrderService;

    private ObjectProphecy $orderContainerFactory;

    private ObjectProphecy $checkoutSessionRepository;

    private ObjectProphecy $debtorExternalDataRepository;

    private ObjectProphecy $sepaClient;

    private ObjectProphecy $trackingEventCollector;

    private CheckoutDeclineOrderUseCase $useCase;

    public function setUp(): void
    {
        $this->workFlowRegistry = $this->prophesize(Registry::class);
        $this->declineOrderService = $this->prophesize(DeclineOrderService::class);
        $this->orderContainerFactory = $this->prophesize(OrderContainerFactory::class);
        $this->checkoutSessionRepository = $this->prophesize(CheckoutSessionRepositoryInterface::class);
        $this->debtorExternalDataRepository = $this->prophesize(DebtorExternalDataRepositoryInterface::class);
        $this->sepaClient = $this->prophesize(SepaClientInterface::class);
        $orderContainer = $this->prophesize(OrderContainer::class);
        $workFlow = $this->prophesize(Workflow::class);

        $this->trackingEventCollector = $this->prophesize(TrackingEventCollector::class);
        $workFlow->can(Argument::cetera())->willReturn(true);

        $order = $this->prophesize(OrderEntity::class);
        $order->getDebtorSepaMandateUuid()->willReturn(Uuid::uuid4());
        $order->getMerchantId()->willReturn(1);
        $externalData = $this->prophesize(DebtorExternalDataEntity::class);
        $externalData->getMerchantExternalId()->willReturn('1');
        $externalData->getName()->willReturn('Billie GmbH');

        $debtorExternalDataAddress = $this->prophesize(AddressEntity::class);
        $debtorExternalDataAddress->getStreet()->willReturn('Wedekingstr.');
        $debtorExternalDataAddress->getHouseNumber()->willReturn('23');
        $debtorExternalDataAddress->getCity()->willReturn('Berlin');
        $debtorExternalDataAddress->getPostalCode()->willReturn('10243');
        $debtorExternalDataAddress->getCountry()->willReturn('DE');

        $orderContainer->getDebtorExternalData()->willReturn($externalData);
        $orderContainer->getOrder()->willReturn($order->reveal());
        $orderContainer->getDebtorExternalDataAddress()->willReturn($debtorExternalDataAddress);

        $this->orderContainerFactory->loadNotYetConfirmedByCheckoutSessionUuid(Argument::any())->willReturn($orderContainer->reveal());
        $this->workFlowRegistry->get(Argument::any())->willReturn($workFlow->reveal());

        $this->useCase = new CheckoutDeclineOrderUseCase(
            $this->workFlowRegistry->reveal(),
            $this->declineOrderService->reveal(),
            $this->orderContainerFactory->reveal(),
            $this->checkoutSessionRepository->reveal(),
            $this->debtorExternalDataRepository->reveal(),
            $this->sepaClient->reveal(),
            $this->trackingEventCollector->reveal()
        );

        $this->useCase->setLogger(new NullLogger());
    }

    /** @test */
    public function shouldSendEventToTheCollector(): void
    {
        $this->declineOrderService->decline(Argument::any())->shouldBeCalled();
        $this->checkoutSessionRepository->reActivateSession(Argument::any())->shouldBeCalled();

        $this->debtorExternalDataRepository->invalidateMerchantExternalId(Argument::any())->shouldBeCalled();
        $this->sepaClient->revokeMandate(Argument::any())->shouldBeCalled();

        $this->trackingEventCollector->collect(Argument::any())->shouldBeCalled();
        self::assertNotEmpty($this->trackingEventCollector->getEvents());

        $this->useCase->execute(new CheckoutDeclineOrderRequest(
            Uuid::uuid4()->toString(),
            CheckoutDeclineOrderRequest::REASON_WRONG_IDENTIFICATION
        ));
    }

    /** @test */
    public function shouldRevokeMandate(): void
    {
        $this->declineOrderService->decline(Argument::any())->shouldBeCalled();
        $this->checkoutSessionRepository->reActivateSession(Argument::any())->shouldBeCalled();

        $this->debtorExternalDataRepository->invalidateMerchantExternalId(Argument::any())->shouldBeCalled();
        $this->sepaClient->revokeMandate(Argument::any())->shouldBeCalled();

        $this->useCase->execute(new CheckoutDeclineOrderRequest(
            Uuid::uuid4()->toString(),
            CheckoutDeclineOrderRequest::REASON_WRONG_IDENTIFICATION
        ));
    }
}
