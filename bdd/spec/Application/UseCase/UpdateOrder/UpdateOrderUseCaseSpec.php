<?php

namespace spec\App\Application\UseCase\UpdateOrder;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\PaellaCoreCriticalException;
use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\Application\UseCase\UpdateOrder\UpdateOrderUseCase;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use DateTime;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateOrderUseCaseSpec extends ObjectBehavior
{
    private const ORDER_EXTERNAL_CODE = "testCode";

    private const ORDER_INVOICE_NUMBER = "tesCode-1234";

    private const ORDER_INVOICE_URL = "/tesCode-1234.pdf";

    private const ORDER_MERCHANT_ID = 1;

    private const ORDER_AMOUNT_GROSS = 1000;

    private const ORDER_AMOUNT_NET = 900;

    private const ORDER_AMOUNT_TAX = 100;

    private const ORDER_DURATION = 50;

    private const ORDER_MERCHANT_DEBTOR_ID = 15;

    public function let(
        OrderPersistenceService $orderPersistenceService,
        BorschtInterface $borscht,
        MerchantDebtorLimitsService $limitsService,
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        OrderStateManager $orderStateManager,
        LoggerInterface $logger,
        OrderEntity $order,
        UpdateOrderRequest $request,
        ValidatorInterface $validator,
        OrderInvoiceManager $invoiceManager
    ) {
        $order->getExternalCode()->willReturn(self::ORDER_EXTERNAL_CODE);
        $order->getInvoiceNumber()->willReturn(self::ORDER_INVOICE_NUMBER);
        $order->getInvoiceUrl()->willReturn(self::ORDER_INVOICE_URL);
        $order->getMerchantId()->willReturn(self::ORDER_MERCHANT_ID);
        $order->getAmountGross()->willReturn(self::ORDER_AMOUNT_GROSS);
        $order->getAmountNet()->willReturn(self::ORDER_AMOUNT_NET);
        $order->getAmountTax()->willReturn(self::ORDER_AMOUNT_TAX);
        $order->getDuration()->willReturn(self::ORDER_DURATION);
        $order->getState()->willReturn(OrderStateManager::STATE_CREATED);
        $order->getMarkedAsFraudAt()->willReturn(null);
        $order->getMerchantDebtorId()->willReturn(self::ORDER_MERCHANT_DEBTOR_ID);

        $request->getMerchantId()->willReturn(self::ORDER_MERCHANT_ID);
        $request->getOrderId()->willReturn(self::ORDER_EXTERNAL_CODE);

        $validator->validate(Argument::any(), Argument::any(), Argument::any())->willReturn(new ConstraintViolationList());

        $this->beConstructedWith(
            $orderPersistenceService,
            $borscht,
            $limitsService,
            $orderRepository,
            $merchantRepository,
            $orderStateManager,
            $invoiceManager
        );

        $this->setLogger($logger);
        $this->setValidator($validator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(UpdateOrderUseCase::class);
    }

    public function it_throws_exception_if_the_order_was_not_found(
        OrderRepositoryInterface $orderRepository,
        UpdateOrderRequest $request
    ) {
        $merchantId = 1;
        $orderExtId = 'notExistingOrderId';

        $request->getMerchantId()->willReturn($merchantId);
        $request->getOrderId()->willReturn($orderExtId);

        $orderRepository->getOneByMerchantIdAndExternalCodeOrUUID($orderExtId, $merchantId)->shouldBeCalled()->willReturn(null);

        $this->shouldThrow(OrderNotFoundException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_the_order_was_marked_as_fraud(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $order->getMarkedAsFraudAt()->willReturn(new DateTime());
        $orderPersistenceService->createFromOrderEntity($order)->shouldBeCalledOnce()->willReturn($orderContainer);

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::ORDER_MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order);

        $this->shouldThrow(FraudOrderException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_on_update_duration_if_the_new_duration_is_less_than_the_current_one(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $request->getDuration()->willReturn(self::ORDER_DURATION - 1);
        $request->getAmountGross()->willReturn(null);
        $request->getInvoiceNumber()->willReturn(null);
        $request->getInvoiceUrl()->willReturn(null);

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::ORDER_MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order);

        $orderPersistenceService->createFromOrderEntity($order)->shouldBeCalledOnce()->willReturn($orderContainer);

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_on_update_duration_if_order_was_not_shipped_or_late(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        OrderStateManager $orderStateManager,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $request->getDuration()->willReturn(self::ORDER_DURATION + 1);
        $request->getAmountGross()->willReturn(null);
        $request->getAmountNet()->willReturn(null);
        $request->getAmountTax()->willReturn(null);
        $request->getInvoiceNumber()->willReturn(null);
        $request->getInvoiceUrl()->willReturn(null);

        $orderStateManager->wasShipped($order)->willReturn(false);
        $orderStateManager->isLate($order)->willReturn(true);

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::ORDER_MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order);

        $orderPersistenceService->createFromOrderEntity($order)->shouldBeCalledOnce()->willReturn($orderContainer);
        $orderContainer->getOrder()->shouldBeCalledOnce()->willReturn($order);

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_updates_order_duration_and_calls_borscht_to_modify_order(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        OrderStateManager $orderStateManager,
        BorschtInterface $borscht,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $newDuration = self::ORDER_DURATION + 1;

        $request->getDuration()->willReturn($newDuration);
        $request->getAmountGross()->willReturn(null);
        $request->getAmountNet()->willReturn(null);
        $request->getAmountTax()->willReturn(null);
        $request->getAmountGross()->willReturn(null);
        $request->getInvoiceNumber()->willReturn(null);
        $request->getInvoiceUrl()->willReturn(null);

        $orderStateManager->wasShipped($order)->willReturn(true);
        $orderStateManager->isLate($order)->willReturn(false);

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::ORDER_MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order);

        $orderPersistenceService->createFromOrderEntity($order)->shouldBeCalledOnce()->willReturn($orderContainer);
        $orderContainer->getOrder()->shouldBeCalledOnce()->willReturn($order);

        $order->setDuration($newDuration)->shouldBeCalled();
        $borscht->modifyOrder($order)->shouldBeCalled();
        $orderRepository->update($order)->shouldBeCalled();

        $this->execute($request);
    }

    public function it_throws_exception_on_update_amount_if_the_new_amounts_are_greater_than_the_current_amounts(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $request->getAmountGross()->willReturn(self::ORDER_AMOUNT_GROSS + 10);
        $request->getAmountNet()->willReturn(self::ORDER_AMOUNT_NET + 10);
        $request->getAmountTax()->willReturn(self::ORDER_AMOUNT_TAX + 10);
        $request->getInvoiceNumber()->willReturn(null);
        $request->getInvoiceUrl()->willReturn(null);

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::ORDER_MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order);

        $orderPersistenceService->createFromOrderEntity($order)->shouldBeCalledOnce()->willReturn($orderContainer);

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_on_update_amount_if_the_order_was_canceled_or_complete(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        OrderStateManager $orderStateManager,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $request->getAmountGross()->willReturn(self::ORDER_AMOUNT_GROSS - 10);
        $request->getAmountNet()->willReturn(self::ORDER_AMOUNT_NET - 10);
        $request->getAmountTax()->willReturn(self::ORDER_AMOUNT_TAX - 10);
        $request->getDuration()->willReturn(null);
        $request->getInvoiceNumber()->willReturn(null);
        $request->getInvoiceUrl()->willReturn(null);

        $orderStateManager->wasShipped($order)->willReturn(false);
        $orderStateManager->isCanceled($order)->willReturn(true);
        $orderStateManager->isComplete($order)->willReturn(true);

        $orderPersistenceService->createFromOrderEntity($order)->shouldBeCalledOnce()->willReturn($orderContainer);
        $orderContainer->getOrder()->shouldBeCalledTimes(2)->willReturn($order);

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::ORDER_MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order);

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_updates_order_amounts_and_invoice_details_then_calls_borscht_to_modify_order(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        OrderStateManager $orderStateManager,
        BorschtInterface $borscht,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $newAmountGross = self::ORDER_AMOUNT_GROSS - 10;
        $newAmountNet = self::ORDER_AMOUNT_NET - 10;
        $newAmountTax = self::ORDER_AMOUNT_TAX - 10;
        $newInvoiceNumber = 'NewInvoiceNum';
        $newInvoiceUrl = '/NewInvoiceNum.pdf';

        $request->getAmountGross()->willReturn($newAmountGross);
        $request->getAmountNet()->willReturn($newAmountNet);
        $request->getAmountTax()->willReturn($newAmountTax);
        $request->getInvoiceNumber()->willReturn($newInvoiceNumber);
        $request->getInvoiceUrl()->willReturn($newInvoiceUrl);
        $request->getDuration()->willReturn(null);

        $orderStateManager->wasShipped($order)->willReturn(true);
        $orderStateManager->isCanceled($order)->willReturn(false);
        $orderStateManager->isComplete($order)->willReturn(false);

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::ORDER_MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order);

        $orderPersistenceService->createFromOrderEntity($order)->shouldBeCalledOnce()->willReturn($orderContainer);
        $orderContainer->getOrder()->shouldBeCalled()->willReturn($order);

        $order->setAmountGross($newAmountGross)->shouldBeCalled()->willReturn($order);
        $order->setAmountNet($newAmountNet)->shouldBeCalled()->willReturn($order);
        $order->setAmountTax($newAmountTax)->shouldBeCalled()->willReturn($order);
        $order->setInvoiceNumber($newInvoiceNumber)->shouldBeCalled()->willReturn($order);
        $order->setInvoiceUrl($newInvoiceUrl)->shouldBeCalled()->willReturn($order);

        $orderRepository->update($order)->shouldBeCalled();

        $borscht->modifyOrder($order)->shouldBeCalled();

        $this->execute($request);
    }

    public function it_updates_order_amounts_and_unlocks_the_debtor_limit(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        OrderStateManager $orderStateManager,
        MerchantRepositoryInterface $merchantRepository,
        UpdateOrderRequest $request,
        OrderEntity $order,
        MerchantEntity $merchant,
        MerchantDebtorLimitsService $limitsService,
        OrderContainer $orderContainer
    ) {
        $newAmountGross = self::ORDER_AMOUNT_GROSS - 10;
        $newAmountNet = self::ORDER_AMOUNT_NET - 10;
        $newAmountTax = self::ORDER_AMOUNT_TAX - 10;
        $amountChanged = self::ORDER_AMOUNT_GROSS - $newAmountGross;

        $request->getAmountGross()->willReturn($newAmountGross);
        $request->getAmountNet()->willReturn($newAmountNet);
        $request->getAmountTax()->willReturn($newAmountTax);
        $request->getDuration()->willReturn(null);
        $request->getInvoiceNumber()->willReturn(null);
        $request->getInvoiceUrl()->willReturn(null);

        $orderStateManager->wasShipped($order)->willReturn(false);
        $orderStateManager->isCanceled($order)->willReturn(false);
        $orderStateManager->isComplete($order)->willReturn(false);

        $orderContainer->getMerchant()->shouldBeCalledTimes(2)->willReturn($merchant);
        $orderContainer->getOrder()->shouldBeCalledTimes(2)->willReturn($order);

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::ORDER_MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order);

        $orderPersistenceService->createFromOrderEntity($order)->shouldBeCalledOnce()->willReturn($orderContainer);

        $order->setAmountGross($newAmountGross)->shouldBeCalled()->willReturn($order);
        $order->setAmountNet($newAmountNet)->shouldBeCalled()->willReturn($order);
        $order->setAmountTax($newAmountTax)->shouldBeCalled()->willReturn($order);

        $orderRepository->update($order)->shouldBeCalled();

        $limitsService->unlock(Argument::any(), Argument::any())->shouldBeCalled();

        $merchant->increaseAvailableFinancingLimit($amountChanged)->shouldBeCalled();
        $merchantRepository->update($merchant)->shouldBeCalled();

        $this->execute($request);
    }

    public function it_throws_exception_on_update_invoice_details_if_the_order_was_canceled_or_completed(
        OrderPersistenceService $orderPersistenceService,
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $newInvoiceNumber = 'NewInvoiceNum';
        $newInvoiceUrl = '/NewInvoiceNum.pdf';

        $request->getInvoiceNumber()->willReturn($newInvoiceNumber);
        $request->getInvoiceUrl()->willReturn($newInvoiceUrl);
        $request->getDuration()->willReturn(null);
        $request->getAmountGross()->willReturn(null);
        $request->getAmountNet()->willReturn(null);
        $request->getAmountTax()->willReturn(null);

        $orderStateManager->wasShipped($order)->willReturn(true);
        $orderStateManager->isCanceled($order)->willReturn(true);
        $orderStateManager->isComplete($order)->willReturn(true);

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::ORDER_MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order);

        $orderPersistenceService->createFromOrderEntity($order)->shouldBeCalledOnce()->willReturn($orderContainer);
        $orderContainer->getOrder()->shouldBeCalledOnce()->willReturn($order);

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_on_update_invoice_details_if_the_order_was_not_shipped(
        OrderPersistenceService $orderPersistenceService,
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $newInvoiceNumber = 'NewInvoiceNum';
        $newInvoiceUrl = '/NewInvoiceNum.pdf';

        $request->getInvoiceNumber()->willReturn($newInvoiceNumber);
        $request->getInvoiceUrl()->willReturn($newInvoiceUrl);
        $request->getDuration()->willReturn(null);
        $request->getAmountGross()->willReturn(null);
        $request->getAmountNet()->willReturn(null);
        $request->getAmountTax()->willReturn(null);

        $orderStateManager->wasShipped($order)->willReturn(false);
        $orderStateManager->isCanceled($order)->willReturn(false);
        $orderStateManager->isComplete($order)->willReturn(false);

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::ORDER_MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order);

        $orderPersistenceService->createFromOrderEntity($order)->shouldBeCalledOnce()->willReturn($orderContainer);
        $orderContainer->getOrder()->shouldBeCalledOnce()->willReturn($order);

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_updates_order_invoice_details_then_calls_borscht_to_modify_order(
        OrderPersistenceService $orderPersistenceService,
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        BorschtInterface $borscht,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $newInvoiceNumber = 'NewInvoiceNum';
        $newInvoiceUrl = '/NewInvoiceNum.pdf';

        $request->getInvoiceNumber()->willReturn($newInvoiceNumber);
        $request->getInvoiceUrl()->willReturn($newInvoiceUrl);
        $request->getDuration()->willReturn(null);
        $request->getAmountGross()->willReturn(null);
        $request->getAmountNet()->willReturn(null);
        $request->getAmountTax()->willReturn(null);

        $orderStateManager->wasShipped($order)->willReturn(true);
        $orderStateManager->isCanceled($order)->willReturn(false);
        $orderStateManager->isComplete($order)->willReturn(false);

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::ORDER_MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order);

        $orderPersistenceService->createFromOrderEntity($order)->shouldBeCalledOnce()->willReturn($orderContainer);
        $orderContainer->getOrder()->shouldBeCalledOnce()->willReturn($order);

        $order->setInvoiceNumber($newInvoiceNumber)->shouldBeCalled()->willReturn($order);
        $order->setInvoiceUrl($newInvoiceUrl)->shouldBeCalled()->willReturn($order);

        $orderRepository->update($order)->shouldBeCalled();

        $borscht->modifyOrder($order)->shouldBeCalled();

        $this->execute($request);
    }
}
