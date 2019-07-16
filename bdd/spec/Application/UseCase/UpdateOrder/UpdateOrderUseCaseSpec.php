<?php

namespace spec\App\Application\UseCase\UpdateOrder;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\PaellaCoreCriticalException;
use App\Application\UseCase\CreateOrder\Request\CreateOrderAmountRequest;
use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\Application\UseCase\UpdateOrder\UpdateOrderUseCase;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsFactory;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use DateTime;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\NullLogger;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateOrderUseCaseSpec extends ObjectBehavior
{
    private const ORDER_ID = 156;

    private const ORDER_EXTERNAL_CODE = "testCode";

    private const ORDER_INVOICE_NUMBER = "tesCode-1234";

    private const ORDER_INVOICE_URL = "/tesCode-1234.pdf";

    private const ORDER_MERCHANT_ID = 1;

    private const ORDER_AMOUNT_GROSS = 1000;

    private const ORDER_AMOUNT_NET = 900;

    private const ORDER_AMOUNT_TAX = 100;

    private const ORDER_DURATION = 50;

    private const ORDER_MERCHANT_DEBTOR_ID = 15;

    private const ORDER_PAYMENT_ID = 'test-pay-id';

    public function let(
        OrderContainerFactory $orderContainerFactory,
        PaymentsServiceInterface $paymentsService,
        MerchantDebtorLimitsService $limitsService,
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        OrderStateManager $orderStateManager,
        OrderInvoiceManager $invoiceManager,
        OrderFinancialDetailsFactory $orderFinancialDetailsFactory,
        OrderFinancialDetailsRepositoryInterface $orderFinancialDetailsRepository,
        OrderContainer $orderContainer,
        OrderEntity $order,
        OrderFinancialDetailsEntity $orderFinancialDetails,
        UpdateOrderRequest $request,
        ValidatorInterface $validator
    ) {
        $order->getId()->willReturn(self::ORDER_ID);
        $order->getExternalCode()->willReturn(self::ORDER_EXTERNAL_CODE);
        $order->getInvoiceNumber()->willReturn(self::ORDER_INVOICE_NUMBER);
        $order->getInvoiceUrl()->willReturn(self::ORDER_INVOICE_URL);
        $order->getMerchantId()->willReturn(self::ORDER_MERCHANT_ID);
        $order->getState()->willReturn(OrderStateManager::STATE_CREATED);
        $order->getMarkedAsFraudAt()->willReturn(null);
        $order->getMerchantDebtorId()->willReturn(self::ORDER_MERCHANT_DEBTOR_ID);

        $orderFinancialDetails->getAmountGross()->willReturn(self::ORDER_AMOUNT_GROSS);
        $orderFinancialDetails->getAmountNet()->willReturn(self::ORDER_AMOUNT_NET);
        $orderFinancialDetails->getAmountTax()->willReturn(self::ORDER_AMOUNT_TAX);
        $orderFinancialDetails->getDuration()->willReturn(self::ORDER_DURATION);

        $orderContainer->getOrder()->willReturn($order);
        $orderContainer->getOrderFinancialDetails()->willReturn($orderFinancialDetails);

        $request->getMerchantId()->willReturn(self::ORDER_MERCHANT_ID);
        $request->getOrderId()->willReturn(self::ORDER_EXTERNAL_CODE);

        $validator->validate(Argument::any(), Argument::any(), Argument::any())->willReturn(new ConstraintViolationList());

        $this->beConstructedWith(...func_get_args());

        $this->setLogger(new NullLogger())->setValidator($validator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(UpdateOrderUseCase::class);
    }

    public function it_throws_exception_if_the_order_was_not_found(
        OrderContainerFactory $orderContainerFactory,
        UpdateOrderRequest $request
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalId(self::ORDER_MERCHANT_ID, self::ORDER_EXTERNAL_CODE)
            ->shouldBeCalled()
            ->willThrow(OrderContainerFactoryException::class)
        ;

        $this->shouldThrow(OrderNotFoundException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_the_order_was_marked_as_fraud(
        OrderContainerFactory $orderContainerFactory,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $order->getMarkedAsFraudAt()->willReturn(new DateTime());
        $orderContainerFactory
            ->loadByMerchantIdAndExternalId(self::ORDER_MERCHANT_ID, self::ORDER_EXTERNAL_CODE)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $this->shouldThrow(FraudOrderException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_on_update_duration_if_the_new_duration_is_less_than_the_current_one(
        OrderContainerFactory $orderContainerFactory,
        UpdateOrderRequest $request,
        OrderContainer $orderContainer
    ) {
        $this->mockRequest($request, self::ORDER_DURATION - 1);

        $orderContainerFactory
            ->loadByMerchantIdAndExternalId(self::ORDER_MERCHANT_ID, self::ORDER_EXTERNAL_CODE)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_on_update_duration_if_order_was_not_shipped_or_late(
        OrderContainerFactory $orderContainerFactory,
        OrderStateManager $orderStateManager,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $this->mockRequest($request, self::ORDER_DURATION + 1);

        $orderStateManager->wasShipped($order)->willReturn(false);
        $orderStateManager->isLate($order)->willReturn(true);

        $orderContainerFactory
            ->loadByMerchantIdAndExternalId(self::ORDER_MERCHANT_ID, self::ORDER_EXTERNAL_CODE)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_updates_order_duration_and_calls_borscht_to_modify_order(
        OrderContainerFactory $orderContainerFactory,
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        OrderFinancialDetailsFactory $orderFinancialDetailsFactory,
        OrderFinancialDetailsRepositoryInterface $orderFinancialDetailsRepository,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $order->getPaymentId()->willReturn(self::ORDER_PAYMENT_ID);
        $newDuration = self::ORDER_DURATION + 1;

        $this->mockRequest($request, $newDuration);

        $orderStateManager->wasShipped($order)->willReturn(true);
        $orderStateManager->isLate($order)->willReturn(false);

        $orderContainerFactory
            ->loadByMerchantIdAndExternalId(self::ORDER_MERCHANT_ID, self::ORDER_EXTERNAL_CODE)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $newOrderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(self::ORDER_AMOUNT_GROSS)
            ->setAmountNet(self::ORDER_AMOUNT_NET)
            ->setAmountTax(self::ORDER_AMOUNT_TAX)
            ->setDuration($newDuration)
        ;

        $orderFinancialDetailsFactory
            ->create(
                self::ORDER_ID,
                self::ORDER_AMOUNT_GROSS,
                self::ORDER_AMOUNT_NET,
                self::ORDER_AMOUNT_TAX,
                $newDuration
            )
            ->shouldBeCalled()
            ->willReturn($newOrderFinancialDetails)
        ;

        $orderFinancialDetailsRepository->insert($newOrderFinancialDetails)->shouldBeCalled();

        $orderContainer->setOrderFinancialDetails($newOrderFinancialDetails)->shouldBeCalled();

        $orderRepository->update($order)->shouldBeCalled();

        $this->execute($request);
    }

    public function it_throws_exception_on_update_amount_if_the_new_amounts_are_greater_than_the_current_amounts(
        OrderContainerFactory $orderContainerFactory,
        UpdateOrderRequest $request,
        OrderContainer $orderContainer
    ) {
        $this->mockRequest($request, null, self::ORDER_AMOUNT_GROSS + 1, self::ORDER_AMOUNT_NET, self::ORDER_AMOUNT_TAX);

        $orderContainerFactory
            ->loadByMerchantIdAndExternalId(self::ORDER_MERCHANT_ID, self::ORDER_EXTERNAL_CODE)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_on_update_amount_if_the_order_was_canceled_or_complete(
        OrderContainerFactory $orderContainerFactory,
        OrderStateManager $orderStateManager,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $this->mockRequest($request, null, self::ORDER_AMOUNT_GROSS - 10, self::ORDER_AMOUNT_NET, self::ORDER_AMOUNT_TAX);

        $orderStateManager->wasShipped($order)->willReturn(false);
        $orderStateManager->isCanceled($order)->willReturn(true);
        $orderStateManager->isComplete($order)->willReturn(true);

        $orderContainerFactory
            ->loadByMerchantIdAndExternalId(self::ORDER_MERCHANT_ID, self::ORDER_EXTERNAL_CODE)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_updates_order_amounts_and_invoice_details_then_calls_borscht_to_modify_order(
        OrderRepositoryInterface $orderRepository,
        OrderContainerFactory $orderContainerFactory,
        OrderStateManager $orderStateManager,
        PaymentsServiceInterface $paymentsService,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderFinancialDetailsFactory $orderFinancialDetailsFactory,
        OrderFinancialDetailsRepositoryInterface $orderFinancialDetailsRepository,
        OrderContainer $orderContainer,
        OrderFinancialDetailsEntity $orderFinancialDetails
    ) {
        $order->getPaymentId()->shouldBeCalledOnce()->willReturn(self::ORDER_PAYMENT_ID);

        $newAmountGross = self::ORDER_AMOUNT_GROSS - 10;
        $newAmountNet = self::ORDER_AMOUNT_NET - 10;
        $newAmountTax = self::ORDER_AMOUNT_TAX - 10;

        $this->mockRequest($request, null, $newAmountGross, $newAmountNet, $newAmountTax);

        $orderStateManager->wasShipped($order)->willReturn(true);
        $orderStateManager->isCanceled($order)->willReturn(false);
        $orderStateManager->isComplete($order)->willReturn(false);

        $orderContainerFactory
            ->loadByMerchantIdAndExternalId(self::ORDER_MERCHANT_ID, self::ORDER_EXTERNAL_CODE)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $newOrderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross($newAmountGross)
            ->setAmountNet($newAmountNet)
            ->setAmountTax($newAmountTax)
            ->setDuration(self::ORDER_DURATION)
        ;

        $orderFinancialDetailsFactory
            ->create(
                self::ORDER_ID,
                $newAmountGross,
                $newAmountNet,
                $newAmountTax,
                self::ORDER_DURATION
            )
            ->shouldBeCalled()
            ->willReturn($newOrderFinancialDetails)
        ;

        $orderFinancialDetailsRepository->insert($newOrderFinancialDetails)->shouldBeCalled();

        $orderContainer->setOrderFinancialDetails($newOrderFinancialDetails)->shouldBeCalled();

        $orderFinancialDetails->getAmountGross()->willReturn($newAmountGross);

        $orderRepository->update($order)->shouldBeCalled();

        $paymentsService
            ->modifyOrder(
                self::ORDER_PAYMENT_ID,
                self::ORDER_DURATION,
                $newAmountGross,
                self::ORDER_INVOICE_NUMBER
            )
            ->shouldBeCalled()
        ;

        $this->execute($request);
    }

    public function it_updates_order_amounts_and_unlocks_the_debtor_limit(
        OrderContainerFactory $orderContainerFactory,
        OrderStateManager $orderStateManager,
        MerchantRepositoryInterface $merchantRepository,
        UpdateOrderRequest $request,
        OrderEntity $order,
        MerchantEntity $merchant,
        MerchantDebtorLimitsService $limitsService,
        OrderFinancialDetailsFactory $orderFinancialDetailsFactory,
        OrderFinancialDetailsRepositoryInterface $orderFinancialDetailsRepository,
        PaymentsServiceInterface $paymentsService,
        OrderContainer $orderContainer
    ) {
        $newAmountGross = self::ORDER_AMOUNT_GROSS - 10;
        $newAmountNet = self::ORDER_AMOUNT_NET - 10;
        $newAmountTax = self::ORDER_AMOUNT_TAX - 10;
        $amountChanged = self::ORDER_AMOUNT_GROSS - $newAmountGross;

        $this->mockRequest($request, null, $newAmountGross, $newAmountNet, $newAmountTax);

        $orderStateManager->wasShipped($order)->willReturn(false);
        $orderStateManager->isCanceled($order)->willReturn(false);
        $orderStateManager->isComplete($order)->willReturn(false);

        $orderContainer->getMerchant()->shouldBeCalledTimes(2)->willReturn($merchant);

        $paymentsService->modifyOrder(Argument::any(), Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();

        $orderContainerFactory
            ->loadByMerchantIdAndExternalId(self::ORDER_MERCHANT_ID, self::ORDER_EXTERNAL_CODE)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $newOrderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross($newAmountGross)
            ->setAmountNet($newAmountNet)
            ->setAmountTax($newAmountTax)
            ->setDuration(self::ORDER_DURATION)
        ;

        $orderFinancialDetailsFactory
            ->create(
                self::ORDER_ID,
                $newAmountGross,
                $newAmountNet,
                $newAmountTax,
                self::ORDER_DURATION
            )
            ->shouldBeCalled()
            ->willReturn($newOrderFinancialDetails)
        ;

        $orderFinancialDetailsRepository->insert($newOrderFinancialDetails)->shouldBeCalled();

        $orderContainer->setOrderFinancialDetails($newOrderFinancialDetails)->shouldBeCalled();

        $limitsService->unlock(Argument::any(), Argument::any())->shouldBeCalled();

        $merchant->increaseAvailableFinancingLimit($amountChanged)->shouldBeCalled();

        $merchantRepository->update($merchant)->shouldBeCalled();

        $this->execute($request);
    }

    public function it_throws_exception_on_update_invoice_details_if_the_order_was_canceled_or_completed(
        OrderContainerFactory $orderContainerFactory,
        OrderStateManager $orderStateManager,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $newInvoiceNumber = 'NewInvoiceNum';
        $newInvoiceUrl = '/NewInvoiceNum.pdf';

        $this->mockRequest($request, null, null, null, null, $newInvoiceNumber, $newInvoiceUrl);

        $orderStateManager->wasShipped($order)->willReturn(false);

        $orderContainerFactory
            ->loadByMerchantIdAndExternalId(self::ORDER_MERCHANT_ID, self::ORDER_EXTERNAL_CODE)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_on_update_invoice_details_if_the_order_was_not_shipped(
        OrderContainerFactory $orderContainerFactory,
        OrderStateManager $orderStateManager,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $newInvoiceNumber = 'NewInvoiceNum';
        $newInvoiceUrl = '/NewInvoiceNum.pdf';

        $this->mockRequest($request, null, null, null, null, $newInvoiceNumber, $newInvoiceUrl);

        $orderStateManager->wasShipped($order)->willReturn(false);
        $orderStateManager->isCanceled($order)->willReturn(false);
        $orderStateManager->isComplete($order)->willReturn(false);

        $orderContainerFactory
            ->loadByMerchantIdAndExternalId(self::ORDER_MERCHANT_ID, self::ORDER_EXTERNAL_CODE)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_updates_order_invoice_details_then_calls_borscht_to_modify_order(
        OrderContainerFactory $orderContainerFactory,
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        PaymentsServiceInterface $paymentsService,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $newInvoiceNumber = 'NewInvoiceNum';
        $newInvoiceUrl = '/NewInvoiceNum.pdf';

        $this->mockRequest($request, null, null, null, null, $newInvoiceNumber, $newInvoiceUrl);

        $orderStateManager->wasShipped($order)->willReturn(true);
        $orderStateManager->isCanceled($order)->willReturn(false);
        $orderStateManager->isComplete($order)->willReturn(false);

        $orderContainerFactory
            ->loadByMerchantIdAndExternalId(self::ORDER_MERCHANT_ID, self::ORDER_EXTERNAL_CODE)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $order->getPaymentId()->shouldBeCalledOnce()->willReturn(self::ORDER_PAYMENT_ID);
        $order->setInvoiceNumber($newInvoiceNumber)->shouldBeCalled()->willReturn($order);
        $order->setInvoiceUrl($newInvoiceUrl)->shouldBeCalled()->willReturn($order);

        $order->getInvoiceNumber()->willReturn($newInvoiceNumber);

        $orderRepository->update($order)->shouldBeCalled();

        $paymentsService
            ->modifyOrder(
                self::ORDER_PAYMENT_ID,
                self::ORDER_DURATION,
                self::ORDER_AMOUNT_GROSS,
                $newInvoiceNumber
            )
            ->shouldBeCalled()
        ;

        $this->execute($request);
    }

    private function mockRequest(
        UpdateOrderRequest $request,
        $duration = null,
        $amountGross = null,
        $amountNet = null,
        $amountTax = null,
        $invoiceNumber = null,
        $invoiceUrl = null
    ) {
        $request->getDuration()->willReturn($duration);
        $request->getAmount()->willReturn(
            (new CreateOrderAmountRequest())
                ->setTax($amountTax)
                ->setNet($amountNet)
                ->setGross($amountGross)
        );
        $request->getInvoiceNumber()->willReturn($invoiceNumber);
        $request->getInvoiceUrl()->willReturn($invoiceUrl);
    }
}
