<?php

namespace spec\App\Application\UseCase\UpdateOrder;

use App\Application\Exception\FraudOrderException;
use App\Application\PaellaCoreCriticalException;
use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\Application\UseCase\UpdateOrder\UpdateOrderUseCase;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\LimitsService;
use App\DomainModel\Order\OrderEntity;
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

    private const ORDER_DEBTOR_ID = 1522;

    public function let(
        BorschtInterface $borscht,
        LimitsService $limitsService,
        OrderRepositoryInterface $orderRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
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
            $borscht,
            $limitsService,
            $orderRepository,
            $merchantDebtorRepository,
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

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_the_order_was_marked_as_fraud(
        OrderRepositoryInterface $orderRepository,
        UpdateOrderRequest $request,
        OrderEntity $order
    ) {
        $order->getMarkedAsFraudAt()->willReturn(new DateTime());

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::ORDER_MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order);

        $this->shouldThrow(FraudOrderException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_on_update_duration_if_the_new_duration_is_less_than_the_current_one(
        OrderRepositoryInterface $orderRepository,
        UpdateOrderRequest $request,
        OrderEntity $order
    ) {
        $request->getDuration()->willReturn(self::ORDER_DURATION - 1);
        $request->getAmountGross()->willReturn(null);
        $request->getInvoiceNumber()->willReturn(null);
        $request->getInvoiceUrl()->willReturn(null);

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::ORDER_MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order);

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_on_update_duration_if_order_was_not_shipped_or_late(
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        UpdateOrderRequest $request,
        OrderEntity $order
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

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_updates_order_duration_and_calls_borscht_to_modify_order(
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        BorschtInterface $borscht,
        UpdateOrderRequest $request,
        OrderEntity $order
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

        $order->setDuration($newDuration)->shouldBeCalled();
        $borscht->modifyOrder($order)->shouldBeCalled();
        $orderRepository->update($order)->shouldBeCalled();

        $this->execute($request);
    }

    public function it_throws_exception_on_update_amount_if_the_new_amounts_are_greater_than_the_current_amounts(
        OrderRepositoryInterface $orderRepository,
        UpdateOrderRequest $request,
        OrderEntity $order
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

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_on_update_amount_if_the_order_was_canceled_or_completed(
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        UpdateOrderRequest $request,
        OrderEntity $order
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

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::ORDER_MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order);

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_updates_order_amounts_and_invoice_details_then_calls_borscht_to_modify_order(
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        BorschtInterface $borscht,
        UpdateOrderRequest $request,
        OrderEntity $order,
        OrderInvoiceManager $invoiceManager
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

        $order->setAmountGross($newAmountGross)->shouldBeCalled()->willReturn($order);
        $order->setAmountNet($newAmountNet)->shouldBeCalled()->willReturn($order);
        $order->setAmountTax($newAmountTax)->shouldBeCalled()->willReturn($order);
        $order->setInvoiceNumber($newInvoiceNumber)->shouldBeCalled()->willReturn($order);
        $order->setInvoiceUrl($newInvoiceUrl)->shouldBeCalled()->willReturn($order);

        $orderRepository->update($order)->shouldBeCalled();

        $invoiceManager->upload($order, 'order.update')->shouldBeCalledOnce();
        $borscht->modifyOrder($order)->shouldBeCalled();

        $this->execute($request);
    }

    public function it_updates_order_amounts_and_unlocks_the_debtor_limit(
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantRepositoryInterface $merchantRepository,
        UpdateOrderRequest $request,
        OrderEntity $order,
        MerchantDebtorEntity $merchantDebtor,
        MerchantEntity $merchant,
        LimitsService $limitsService
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

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::ORDER_MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order);

        $order->setAmountGross($newAmountGross)->shouldBeCalled()->willReturn($order);
        $order->setAmountNet($newAmountNet)->shouldBeCalled()->willReturn($order);
        $order->setAmountTax($newAmountTax)->shouldBeCalled()->willReturn($order);

        $orderRepository->update($order)->shouldBeCalled();

        $merchantDebtor->getDebtorId()->willReturn(self::ORDER_DEBTOR_ID);
        $merchantDebtorRepository->getOneById(self::ORDER_MERCHANT_DEBTOR_ID)->shouldBeCalled()->willReturn($merchantDebtor);

        $limitsService->unlock($merchantDebtor, $amountChanged)->shouldBeCalled()->willReturn(true);

        $merchantRepository->getOneById(self::ORDER_MERCHANT_ID)->willReturn($merchant);

        $merchant->increaseAvailableFinancingLimit($amountChanged)->shouldBeCalled();
        $merchantRepository->update($merchant)->shouldBeCalled();

        $this->execute($request);
    }

    public function it_throws_exception_on_update_invoice_details_if_the_order_was_canceled_or_completed(
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        UpdateOrderRequest $request,
        OrderEntity $order
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

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_on_update_invoice_details_if_the_order_was_not_shipped(
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        UpdateOrderRequest $request,
        OrderEntity $order
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

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_updates_order_invoice_details_then_calls_borscht_to_modify_order(
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        BorschtInterface $borscht,
        UpdateOrderRequest $request,
        OrderEntity $order
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

        $order->setInvoiceNumber($newInvoiceNumber)->shouldBeCalled()->willReturn($order);
        $order->setInvoiceUrl($newInvoiceUrl)->shouldBeCalled()->willReturn($order);

        $orderRepository->update($order)->shouldBeCalled();

        $borscht->modifyOrder($order)->shouldBeCalled();

        $this->execute($request);
    }
}
