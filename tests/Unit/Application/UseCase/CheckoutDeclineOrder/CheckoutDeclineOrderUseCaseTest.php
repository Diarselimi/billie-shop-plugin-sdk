<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\CheckoutDeclineOrder;

use App\Application\UseCase\CheckoutDeclineOrder\CheckoutDeclineOrderRequest;
use App\Application\UseCase\CheckoutDeclineOrder\CheckoutDeclineOrderUseCase;
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
        $workFlow->can(Argument::cetera())->willReturn(true);

        $order = $this->prophesize(OrderEntity::class);
        $order->getDebtorSepaMandateUuid()->willReturn(Uuid::uuid4());
        $externalData = $this->prophesize(DebtorExternalDataEntity::class);
        $externalData->getMerchantExternalId()->willReturn('1');

        $orderContainer->getDebtorExternalData()->willReturn($externalData);
        $orderContainer->getOrder()->willReturn($order->reveal());
        $this->orderContainerFactory->loadNotYetConfirmedByCheckoutSessionUuid(Argument::any())->willReturn($orderContainer->reveal());
        $this->workFlowRegistry->get(Argument::any())->willReturn($workFlow->reveal());

        $this->useCase = new CheckoutDeclineOrderUseCase(
            $this->workFlowRegistry->reveal(),
            $this->declineOrderService->reveal(),
            $this->orderContainerFactory->reveal(),
            $this->checkoutSessionRepository->reveal(),
            $this->debtorExternalDataRepository->reveal(),
            $this->sepaClient->reveal()
        );

        $this->useCase->setLogger(new NullLogger());
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
