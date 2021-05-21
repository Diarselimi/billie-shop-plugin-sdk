<?php

namespace spec\App\DomainModel\OrderUpdate;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\LegacyUpdateOrder\LegacyUpdateOrderRequest;
use App\DomainModel\Invoice\CreditNote\CreditNote;
use App\DomainModel\Invoice\CreditNote\CreditNoteFactory;
use App\DomainModel\Invoice\CreditNote\InvoiceCreditNoteMessageFactory;
use App\DomainModel\Invoice\ExtendInvoiceService;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsPersistenceService;
use App\DomainModel\OrderInvoiceDocument\InvoiceDocumentUploadException;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\InvoiceDocumentUploadHandlerAggregator;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\InvoiceDocumentUploadHandlerInterface;
use App\DomainModel\OrderUpdate\UpdateOrderException;
use App\DomainModel\OrderUpdate\UpdateOrderLimitsService;
use App\DomainModel\OrderUpdate\LegacyUpdateOrderService;
use App\DomainModel\OrderUpdate\UpdateOrderRequestValidator;
use App\DomainModel\Payment\PaymentRequestFactory;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Payment\RequestDTO\ModifyRequestDTO;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;
use Ozean12\Transfer\Message\CreditNote\CreateCreditNote;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class LegacyUpdateOrderServiceSpec extends ObjectBehavior
{
    private const ORDER_UUID = '4ea7ca9a-876f-478f-9fce-441effaac58d';

    public function it_is_initializable()
    {
        $this->shouldHaveType(LegacyUpdateOrderService::class);
    }

    public function let(
        PaymentsServiceInterface $paymentsService,
        OrderRepositoryInterface $orderRepository,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        InvoiceDocumentUploadHandlerAggregator $invoiceUrlHandler,
        PaymentRequestFactory $paymentRequestFactory,
        UpdateOrderLimitsService $updateOrderLimitsService,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        ExtendInvoiceService $extendInvoiceService,
        InvoiceCreditNoteMessageFactory $creditNoteAnnouncer,
        CreditNoteFactory $creditNoteFactory,
        MessageBusInterface $bus,
        LoggerInterface $logger,
        OrderContainer $orderContainer,
        OrderEntity $order
    ) {
        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger);

        $orderContainer->getOrder()->willReturn($order);
        $order->getUuid()->willReturn(self::ORDER_UUID);
        $order->isWorkflowV1()->willReturn(true);
        $order->isWorkflowV2()->willReturn(false);
        $order->getState()->willReturn(OrderEntity::STATE_CREATED);
        $order->setInvoiceNumber(Argument::any())->willReturn($order);
        $order->setInvoiceUrl(Argument::any())->willReturn($order);
        $order->setExternalCode(Argument::any())->willReturn($order);
        $bus->dispatch(Argument::cetera())->willReturn(new Envelope(new CreateCreditNote()));
    }

    public function it_updates_amount_but_not_limits_if_order_was_shipped(
        OrderContainer $orderContainer,
        OrderEntity $order,
        LegacyUpdateOrderRequest $request,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        UpdateOrderLimitsService $updateOrderLimitsService,
        MerchantEntity $merchant,
        MerchantRepositoryInterface $merchantRepository,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderRepositoryInterface $orderRepository,
        InvoiceDocumentUploadHandlerAggregator $invoiceUrlHandler,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentsServiceInterface $paymentsService
    ) {
        $order->getInvoiceNumber()->willReturn('123');
        $order->getInvoiceUrl()->willReturn('some_url');
        $changeSet = (new LegacyUpdateOrderRequest('order123', 1))->setAmount(
            TaxedMoneyFactory::create(150, 150, 0)
        );
        $orderContainer->getInvoices()->willReturn(new InvoiceCollection([]));
        $orderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(200))
            ->setAmountNet(new Money(200))
            ->setAmountTax(new Money(0))
            ->setUnshippedAmountGross(new Money())
            ->setUnshippedAmountNet(new Money())
            ->setUnshippedAmountTax(new Money())
            ->setDuration(30)
            ->setOrderId(1);
        $newOrderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(150))
            ->setAmountNet(new Money(150))
            ->setAmountTax(new Money(0))
            ->setUnshippedAmountGross(new Money())
            ->setUnshippedAmountNet(new Money())
            ->setUnshippedAmountTax(new Money())
            ->setDuration(30)
            ->setOrderId(1);

        $grossDiff = new Money(50);
        $updateOrderRequestValidator->getValidatedRequest(
            $orderContainer,
            $request
        )->shouldBeCalled()->willReturn($changeSet);
        $orderContainer->getOrderFinancialDetails()->shouldBeCalled()->willReturn($orderFinancialDetails);
        $orderContainer->getMerchant()->shouldNotBeCalled();

        // should NOT unlock merchant debtor limit
        $updateOrderLimitsService->updateLimitAmounts($orderContainer, Argument::any())->shouldNotBeCalled();

        // should NOT unlock merchant limit
        $merchant->increaseFinancingLimit($grossDiff)->shouldNotBeCalled();
        $merchantRepository->update($merchant)->shouldNotBeCalled();

        // update financial details
        $financialDetailsPersistenceService->updateFinancialDetails(
            $orderContainer,
            $changeSet,
            $newOrderFinancialDetails->getDuration()
        )->shouldBeCalled();

        // should NOT update order nor invoice
        $orderRepository->update(Argument::any())->shouldNotBeCalled();
        $invoiceUrlHandler->handle(Argument::cetera())->shouldNotBeCalled();

        // calls payments service
        $order->wasShipped()->shouldBeCalled()->willReturn(true);
        $paymentsModifyRequest = new ModifyRequestDTO();
        $paymentRequestFactory->createModifyRequestDTO($orderContainer)
            ->shouldBeCalled()->willReturn($paymentsModifyRequest);
        $paymentsService->modifyOrder($paymentsModifyRequest)->shouldBeCalled();

        $this->update($orderContainer, $request);
    }

    public function it_updates_amount_and_limits_if_order_was_not_shipped(
        OrderContainer $orderContainer,
        OrderEntity $order,
        LegacyUpdateOrderRequest $request,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        UpdateOrderLimitsService $updateOrderLimitsService,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderRepositoryInterface $orderRepository,
        InvoiceDocumentUploadHandlerAggregator $invoiceUrlHandler,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentsServiceInterface $paymentsService
    ) {
        $changeSet = (new LegacyUpdateOrderRequest('order123', 1))->setAmount(
            TaxedMoneyFactory::create(150, 150, 0)
        );
        $orderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(200))
            ->setAmountNet(new Money(200))
            ->setAmountTax(new Money(0))
            ->setDuration(30)
            ->setOrderId(1);
        $newOrderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(150))
            ->setAmountNet(new Money(150))
            ->setAmountTax(new Money(0))
            ->setDuration(30)
            ->setOrderId(1);

        $updateOrderRequestValidator->getValidatedRequest(
            $orderContainer,
            $request
        )->shouldBeCalled()->willReturn($changeSet);
        $orderContainer->getOrderFinancialDetails()->shouldBeCalled()->willReturn($orderFinancialDetails);

        // unlocks merchant debtor limit
        $updateOrderLimitsService->updateLimitAmounts($orderContainer, $changeSet->getAmount())->shouldBeCalled();

        // update financial details
        $financialDetailsPersistenceService->updateFinancialDetails(
            $orderContainer,
            $changeSet,
            $newOrderFinancialDetails->getDuration()
        )->shouldBeCalled();

        // should NOT update order nor invoice
        $orderRepository->update(Argument::any())->shouldNotBeCalled();
        $invoiceUrlHandler->handle(Argument::cetera())->shouldNotBeCalled();

        // should NOT call payments service
        $order->wasShipped()->shouldBeCalled()->willReturn(false);
        $paymentRequestFactory->createModifyRequestDTO(Argument::any())->shouldNotBeCalled();
        $paymentsService->modifyOrder(Argument::any())->shouldNotBeCalled();

        $orderContainer->getInvoices()->willReturn(new InvoiceCollection([]));

        $this->update($orderContainer, $request);
    }

    public function it_updates_duration(
        OrderContainer $orderContainer,
        OrderEntity $order,
        LegacyUpdateOrderRequest $request,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        UpdateOrderLimitsService $updateOrderLimitsService,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderRepositoryInterface $orderRepository,
        InvoiceDocumentUploadHandlerAggregator $invoiceUrlHandler,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentsServiceInterface $paymentsService,
        Invoice $invoice,
        InvoiceCollection $invoiceCollection
    ) {
        $invoiceCollection->getLastInvoice()->willReturn($invoice);
        $invoiceCollection->isEmpty()->willReturn(false);
        $orderContainer->getInvoices()->willReturn($invoiceCollection);
        $changeSet = (new LegacyUpdateOrderRequest('order123', 1))->setDuration(60);
        $newOrderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(200))
            ->setAmountNet(new Money(200))
            ->setAmountTax(new Money(0))
            ->setDuration(60)
            ->setOrderId(1);

        $updateOrderRequestValidator->getValidatedRequest(
            $orderContainer,
            $request
        )->shouldBeCalled()->willReturn($changeSet);

        // it does NOT unlock limits
        $updateOrderLimitsService->updateLimitAmounts($orderContainer, Argument::any())->shouldNotBeCalled();

        // update financial details
        $financialDetailsPersistenceService->updateFinancialDetails(
            $orderContainer,
            $changeSet,
            $newOrderFinancialDetails->getDuration()
        )->shouldBeCalled();

        // should NOT update order nor invoice
        $orderRepository->update(Argument::any())->shouldNotBeCalled();
        $invoiceUrlHandler->handle(Argument::cetera())->shouldNotBeCalled();

        // calls payments service
        $order->wasShipped()->shouldBeCalled()->willReturn(true);
        $paymentsModifyRequest = new ModifyRequestDTO();
        $paymentRequestFactory->createModifyRequestDTO($orderContainer)->shouldBeCalled()->willReturn($paymentsModifyRequest);
        $paymentsService->modifyOrder($paymentsModifyRequest)->shouldBeCalled();

        $this->update($orderContainer, $request);
    }

    public function it_updates_invoice(
        OrderContainer $orderContainer,
        OrderEntity $order,
        LegacyUpdateOrderRequest $request,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        UpdateOrderLimitsService $updateOrderLimitsService,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderRepositoryInterface $orderRepository,
        InvoiceDocumentUploadHandlerAggregator $invoiceUrlHandler,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentsServiceInterface $paymentsService,
        Invoice $invoice,
        InvoiceCollection $invoiceCollection
    ) {
        $invoice->setExternalCode(Argument::any())->shouldBeCalled()->willReturn($invoice);
        $invoice->getDuration()->willReturn(30);
        $invoiceCollection->getLastInvoice()->willReturn($invoice);
        $invoiceCollection->isEmpty()->willReturn(false);
        $orderContainer->getInvoices()->willReturn($invoiceCollection);

        $order->getInvoiceNumber()->willReturn('123');
        $order->getInvoiceUrl()->willReturn('some_url');
        $changeSet = (new LegacyUpdateOrderRequest('order123', 1))->setInvoiceNumber('foobar')->setInvoiceUrl('foobar.pdf');
        $updateOrderRequestValidator->getValidatedRequest(
            $orderContainer,
            $request
        )->shouldBeCalled()->willReturn($changeSet);

        // it does NOT unlock limits
        $updateOrderLimitsService->updateLimitAmounts($orderContainer, Argument::any())->shouldNotBeCalled();

        // it does NOT update financial details
        $financialDetailsPersistenceService->updateFinancialDetails(
            Argument::cetera()
        )->shouldNotBeCalled();

        // update order and invoice
        $orderRepository->update($order)->shouldBeCalled();
        $invoiceUrlHandler->handle($order, self::ORDER_UUID, 'some_url', '123', 'order.update')->shouldBeCalled();

        // calls payments service
        $order->wasShipped()->shouldBeCalled()->willReturn(true);
        $paymentsModifyRequest = new ModifyRequestDTO();
        $paymentRequestFactory->createModifyRequestDTO($orderContainer)->shouldBeCalled()->willReturn($paymentsModifyRequest);
        $paymentsService->modifyOrder($paymentsModifyRequest)->shouldBeCalled();

        $this->update($orderContainer, $request);
    }

    public function it_updates_external_code(
        OrderContainer $orderContainer,
        OrderEntity $order,
        LegacyUpdateOrderRequest $request,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        UpdateOrderLimitsService $updateOrderLimitsService,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderRepositoryInterface $orderRepository,
        InvoiceDocumentUploadHandlerAggregator $invoiceUrlHandler,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentsServiceInterface $paymentsService
    ) {
        $changeSet = (new LegacyUpdateOrderRequest('order123', 1))->setExternalCode('foobar001');
        $orderContainer->getInvoices()->willReturn(new InvoiceCollection([]));

        $updateOrderRequestValidator->getValidatedRequest(
            $orderContainer,
            $request
        )->shouldBeCalled()->willReturn($changeSet);

        // it does NOT unlock limits
        $updateOrderLimitsService->updateLimitAmounts($orderContainer, Argument::any())->shouldNotBeCalled();

        // it does NOT update financial details
        $financialDetailsPersistenceService->updateFinancialDetails(
            Argument::cetera()
        )->shouldNotBeCalled();

        // update order
        $orderRepository->update($order)->shouldBeCalled();

        // it does NOT update invoice
        $invoiceUrlHandler->handle(Argument::cetera())->shouldNotBeCalled();

        // it does not call payments service
        $order->wasShipped()->shouldBeCalled()->willReturn(true);
        $paymentRequestFactory->createModifyRequestDTO(Argument::any())->shouldNotBeCalled();
        $paymentsService->modifyOrder(Argument::any())->shouldNotBeCalled();

        $this->update($orderContainer, $request);
    }

    public function it_does_not_update_anything(
        OrderContainer $orderContainer,
        LegacyUpdateOrderRequest $request,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        UpdateOrderLimitsService $updateOrderLimitsService,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderRepositoryInterface $orderRepository,
        InvoiceDocumentUploadHandlerAggregator $invoiceUrlHandler,
        OrderEntity $order,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentsServiceInterface $paymentsService
    ) {
        $changeSet = (new LegacyUpdateOrderRequest('order123', 1));
        $updateOrderRequestValidator->getValidatedRequest(
            $orderContainer,
            $request
        )->shouldBeCalled()->willReturn($changeSet);
        $orderContainer->getInvoices()->willReturn(new InvoiceCollection([]));

        // it does NOT unlock limits
        $updateOrderLimitsService->updateLimitAmounts($orderContainer, Argument::any())->shouldNotBeCalled();

        // it does NOT update financial details
        $financialDetailsPersistenceService->updateFinancialDetails(
            Argument::cetera()
        )->shouldNotBeCalled();

        // it does NOT update order
        $orderRepository->update(Argument::any())->shouldNotBeCalled();

        // it does NOT update invoice
        $invoiceUrlHandler->handle(Argument::cetera())->shouldNotBeCalled();

        // it does not call payments service
        $order->wasShipped(Argument::any())->shouldBeCalled()->willReturn(true);
        $paymentRequestFactory->createModifyRequestDTO(Argument::any())->shouldNotBeCalled();
        $paymentsService->modifyOrder(Argument::any())->shouldNotBeCalled();

        $this->update($orderContainer, $request);
    }

    public function it_does_not_call_payments_if_order_was_not_shipped(
        OrderContainer $orderContainer,
        OrderEntity $order,
        LegacyUpdateOrderRequest $request,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        UpdateOrderLimitsService $updateOrderLimitsService,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderRepositoryInterface $orderRepository,
        InvoiceDocumentUploadHandlerAggregator $invoiceUrlHandler,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentsServiceInterface $paymentsService
    ) {
        $changeSet = (new LegacyUpdateOrderRequest('order123', 1))->setAmount(
            TaxedMoneyFactory::create(150, 150, 0)
        );
        $orderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(200))
            ->setAmountNet(new Money(200))
            ->setAmountTax(new Money(0))
            ->setDuration(30)
            ->setOrderId(1);
        $newOrderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(150))
            ->setAmountNet(new Money(150))
            ->setAmountTax(new Money(0))
            ->setDuration(30)
            ->setOrderId(1);

        $orderContainer->getInvoices()->willReturn(new InvoiceCollection([]));

        $updateOrderRequestValidator->getValidatedRequest(
            $orderContainer,
            $request
        )->shouldBeCalled()->willReturn($changeSet);
        $orderContainer->getOrderFinancialDetails()->shouldBeCalled()->willReturn($orderFinancialDetails);

        // unlocks merchant debtor limit
        $updateOrderLimitsService->updateLimitAmounts($orderContainer, $changeSet->getAmount())->shouldBeCalled();

        // update financial details
        $financialDetailsPersistenceService->updateFinancialDetails(
            $orderContainer,
            $changeSet,
            $newOrderFinancialDetails->getDuration()
        )->shouldBeCalled();

        // should NOT update order nor invoice
        $orderRepository->update(Argument::any())->shouldNotBeCalled();
        $invoiceUrlHandler->handle(Argument::cetera())->shouldNotBeCalled();

        // it does NOT call payments service
        $order->wasShipped()->shouldBeCalled()->willReturn(false);
        $paymentRequestFactory->createModifyRequestDTO(Argument::any())->shouldNotBeCalled();
        $paymentsService->modifyOrder(Argument::any())->shouldNotBeCalled();

        $this->update($orderContainer, $request);
    }

    public function it_fails_on_upload_invoice(
        OrderContainer $orderContainer,
        OrderEntity $order,
        LegacyUpdateOrderRequest $request,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        UpdateOrderLimitsService $updateOrderLimitsService,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderRepositoryInterface $orderRepository,
        InvoiceDocumentUploadHandlerAggregator $invoiceUrlHandler,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentsServiceInterface $paymentsService
    ) {
        $order->getInvoiceNumber()->willReturn('123');
        $order->getInvoiceUrl()->willReturn('some_url');
        $changeSet = (new LegacyUpdateOrderRequest('order123', 1))->setInvoiceNumber('foobar')->setInvoiceUrl('foobar.pdf');
        $updateOrderRequestValidator->getValidatedRequest(
            $orderContainer,
            $request
        )->shouldBeCalled()->willReturn($changeSet);
        $orderContainer->getInvoices()->willReturn(new InvoiceCollection([]));

        // it does NOT unlock limits
        $updateOrderLimitsService->updateLimitAmounts($orderContainer, Argument::any())->shouldNotBeCalled();

        // it does NOT update financial details
        $financialDetailsPersistenceService->updateFinancialDetails(
            Argument::any(),
            InvoiceDocumentUploadHandlerInterface::EVENT_SOURCE_UPDATE
        )->shouldNotBeCalled();

        // update order and invoice
        $orderRepository->update($order)->shouldBeCalled();
        $invoiceUrlHandler->handle(Argument::cetera())
            ->shouldBeCalled()->willThrow(InvoiceDocumentUploadException::class);

        // it does NOT call payments service
        $order->wasShipped()->shouldNotBeCalled();
        $paymentRequestFactory->createModifyRequestDTO(Argument::any())->shouldNotBeCalled();
        $paymentsService->modifyOrder(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(UpdateOrderException::class)->during('update', [$orderContainer, $request]);
    }

    public function it_fails_on_workflow_v2(
        OrderContainer $orderContainer,
        OrderEntity $order,
        LegacyUpdateOrderRequest $request
    ) {
        $order->isWorkflowV1()->willReturn(false);
        $order->isWorkflowV2()->willReturn(true);
        $this->shouldThrow(WorkflowException::class)->during('update', [$orderContainer, $request]);
    }

    public function it_uses_extend_invoice_service_if_butler_invoice_is_found(
        OrderContainer $orderContainer,
        OrderEntity $order,
        LegacyUpdateOrderRequest $request,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        UpdateOrderLimitsService $updateOrderLimitsService,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderRepositoryInterface $orderRepository,
        InvoiceDocumentUploadHandlerAggregator $invoiceUrlHandler,
        ExtendInvoiceService $extendInvoiceService,
        InvoiceCreditNoteMessageFactory $creditNoteAnnouncer,
        LegacyUpdateOrderRequest $changeSet,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentsServiceInterface $paymentsService,
        CreditNoteFactory $creditNoteFactory
    ) {
        $creditNoteFactory->create(Argument::cetera())->willReturn($this->prepareCreditNote());
        $changeSet->getAmount()->willReturn(TaxedMoneyFactory::create(150, 150, 0));
        $changeSet->getDuration()->willReturn(30);
        $changeSet->isDurationChanged()->willReturn(true);
        $changeSet->isAmountChanged()->willReturn(true);
        $changeSet->isInvoiceNumberChanged()->willReturn(false);
        $changeSet->isExternalCodeChanged()->willReturn(false);
        $changeSet->isInvoiceUrlChanged()->willReturn(false);
        $changeSet->setAmount(Argument::any())->willReturn($changeSet);

        $orderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(200))
            ->setAmountNet(new Money(200))
            ->setAmountTax(new Money(0))
            ->setDuration(30)
            ->setOrderId(1);
        $newOrderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(150))
            ->setAmountNet(new Money(150))
            ->setAmountTax(new Money(0))
            ->setDuration(30)
            ->setOrderId(1);

        $updateOrderRequestValidator->getValidatedRequest(
            $orderContainer,
            $request
        )->shouldBeCalled()->willReturn($changeSet);
        $orderContainer->getOrderFinancialDetails()->willReturn($orderFinancialDetails);

        // unlocks merchant debtor limit
        $updateOrderLimitsService->updateLimitAmounts(Argument::cetera())->shouldNotBeCalled();

        // update financial details
        $financialDetailsPersistenceService->updateFinancialDetails(
            $orderContainer,
            $changeSet,
            $newOrderFinancialDetails->getDuration()
        )->shouldBeCalled();

        // should NOT update order nor invoice
        $orderRepository->update(Argument::any())->shouldNotBeCalled();
        $invoiceUrlHandler->handle(Argument::cetera())->shouldNotBeCalled();

        // it does NOT call payments service
        $invoice = new Invoice();
        $invoice
            ->setUuid(Uuid::uuid4()->toString())
            ->setAmount(TaxedMoneyFactory::create(100, 50, 10))
            ->setExternalCode('some_code');
        $orderContainer->getInvoices()->shouldBeCalled()->willReturn(new InvoiceCollection([$invoice]));
        $order->wasShipped()->shouldBeCalled()->willReturn(true);
        $extendInvoiceService->extend(Argument::cetera())->shouldBeCalled();

        $creditNoteAnnouncer->create(Argument::cetera())->shouldBeCalledOnce();

        $paymentsModifyRequest = new ModifyRequestDTO();
        $paymentRequestFactory->createModifyRequestDTO($orderContainer)->shouldBeCalled()->willReturn($paymentsModifyRequest);
        $paymentsService->modifyOrder($paymentsModifyRequest)->shouldBeCalled();

        $this->update($orderContainer, $request);
    }

    private function prepareCreditNote(): CreditNote
    {
        return (new CreditNote())
            ->setUuid(self::ORDER_UUID)
            ->setExternalCode('CORE')
            ->setCreatedAt(new \DateTime())
            ->setAmount(TaxedMoneyFactory::create(123, 22, 1));
    }
}
