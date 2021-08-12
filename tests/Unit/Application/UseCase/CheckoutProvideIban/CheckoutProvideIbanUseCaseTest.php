<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\CheckoutProvideIban;

use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\CheckoutProvideIban\CheckoutProvideIbanNotAllowedException;
use App\Application\UseCase\CheckoutProvideIban\CheckoutProvideIbanRequest;
use App\Application\UseCase\CheckoutProvideIban\CheckoutProvideIbanResponse;
use App\Application\UseCase\CheckoutProvideIban\CheckoutProvideIbanUseCase;
use App\DomainModel\Iban\IbanFraudCheck;
use App\DomainModel\Mandate\SepaMandateGenerator;
use App\DomainModel\Order\Lifecycle\DeclineOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\Tests\Integration\IntegrationTestCase;
use Ozean12\Sepa\Client\DomainModel\Mandate\SepaMandate;
use Ozean12\Support\ValueObject\BankAccount;
use Ozean12\Support\ValueObject\Iban;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;
use Webmozart\Assert\Assert;

/**
 * @see GetInvoicePaymentsUseCase
 */
class CheckoutProvideIbanUseCaseTest extends IntegrationTestCase
{
    private const SESSION_UUID = '72a74d7d-0bc7-45c3-8bb6-1e1d8aef1fc7';

    private const IBAN = 'DE42500105172497563393';

    /**
     * @var OrderContainerFactory|ObjectProphecy
     */
    private ObjectProphecy $orderContainerFactory;

    /**
     * @var OrderRepositoryInterface|ObjectProphecy
     */
    private ObjectProphecy $orderRepository;

    /**
     * @var IbanFraudCheck|ObjectProphecy
     */
    private ObjectProphecy $ibanFraudCheck;

    /**
     * @var Registry|ObjectProphecy
     */
    private ObjectProphecy $workflowRegistry;

    /**
     * @var DeclineOrderService|ObjectProphecy
     */
    private ObjectProphecy $declineOrderService;

    /**
     * @var SepaMandateGenerator|ObjectProphecy
     */
    private ObjectProphecy $sepaMandateGenerator;

    /**
     * @var OrderContainer|ObjectProphecy
     */
    private ObjectProphecy $orderContainer;

    private CheckoutProvideIbanUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderContainerFactory = $this->prophesize(OrderContainerFactory::class);
        $this->orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $this->ibanFraudCheck = $this->prophesize(IbanFraudCheck::class);
        $this->workflowRegistry = $this->prophesize(Registry::class);
        $this->declineOrderService = $this->prophesize(DeclineOrderService::class);
        $this->sepaMandateGenerator = $this->prophesize(SepaMandateGenerator::class);
        $this->orderContainer = $this->prophesize(OrderContainer::class);

        $this->useCase = new CheckoutProvideIbanUseCase(
            $this->orderContainerFactory->reveal(),
            $this->orderRepository->reveal(),
            $this->ibanFraudCheck->reveal(),
            $this->workflowRegistry->reveal(),
            $this->declineOrderService->reveal(),
            $this->sepaMandateGenerator->reveal()
        );

        $this->useCase->setValidator($this->getContainer()->get(ValidatorInterface::class));
    }

    /**
     * @test
     */
    public function shouldFailWithInvalidIban(): void
    {
        $this->expectException(RequestValidationException::class);
        $this->orderContainerFactory->loadNotYetConfirmedByCheckoutSessionUuid(self::SESSION_UUID)->shouldBeCalledOnce()->willReturn($this->orderContainer);

        $this->useCase->execute(
            new CheckoutProvideIbanRequest(self::SESSION_UUID, null)
        );
    }

    /**
     * @test
     */
    public function shouldStopOnFraudIban(): void
    {
        $this->expectException(CheckoutProvideIbanNotAllowedException::class);

        $order = $this->prophesize(OrderEntity::class);
        $this->orderContainer->getOrder()->shouldBeCalled()->willReturn($order);

        $this->orderContainerFactory->loadNotYetConfirmedByCheckoutSessionUuid(self::SESSION_UUID)->shouldBeCalledOnce()->willReturn($this->orderContainer);
        $this->ibanFraudCheck->check(Argument::cetera())->shouldBeCalledOnce()->willReturn(false);
        $this->declineOrderService->decline($this->orderContainer)->shouldBeCalledOnce();

        $workflow = $this->prophesize(Workflow::class);
        $workflow->can($order, OrderEntity::TRANSITION_DECLINE)->shouldBeCalledOnce()->willReturn(true);
        $this->workflowRegistry->get($order)->shouldBeCalledOnce()->willReturn($workflow);

        $this->useCase->execute(
            new CheckoutProvideIbanRequest(self::SESSION_UUID, self::IBAN)
        );
    }

    /**
     * @test
     */
    public function shouldGenerateAndReturnMandate(): void
    {
        $order = $this->prophesize(OrderEntity::class);
        $order->setDebtorSepaMandateUuid(Argument::any())->willReturn($order);

        $this->orderContainer->getOrder()->shouldBeCalled()->willReturn($order);

        $iban = new Iban(self::IBAN);
        $bankAccount = new BankAccount($iban, 'bic', null, null);

        $mandate = $this->prophesize(SepaMandate::class);
        $mandate->getUuid()->willReturn(Uuid::uuid4());
        $mandate->getCreditorIdentification()->willReturn(Uuid::uuid4()->toString());
        $mandate->getMandateReference()->willReturn(Uuid::uuid4()->toString());
        $mandate->getBankAccount()->willReturn($bankAccount);

        $this->orderContainerFactory->loadNotYetConfirmedByCheckoutSessionUuid(self::SESSION_UUID)->shouldBeCalledOnce()->willReturn($this->orderContainer);
        $this->ibanFraudCheck->check(Argument::cetera())->shouldBeCalledOnce()->willReturn(true);
        $this->sepaMandateGenerator->generateForOrderContainer($this->orderContainer, Argument::that(function (Iban $iban) {
            return $iban->toString() === self::IBAN;
        }))->shouldBeCalledOnce()->willReturn($mandate);

        $this->orderRepository->update($order)->shouldBeCalledOnce();

        $response = $this->useCase->execute(
            new CheckoutProvideIbanRequest(self::SESSION_UUID, self::IBAN)
        );

        Assert::isInstanceOf($response, CheckoutProvideIbanResponse::class);
    }
}
