<?php

namespace spec\App\Application\UseCase\CancelOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\CancelOrder\CancelOrderException;
use App\Application\UseCase\CancelOrder\CancelOrderRequest;
use App\Application\UseCase\CancelOrder\LegacyCancelOrderUseCase;
use App\DomainModel\Invoice\CreditNote\CreditNote;
use App\DomainModel\Invoice\CreditNote\CreditNoteCollection;
use App\DomainModel\Invoice\CreditNote\CreditNoteFactory;
use App\DomainModel\Invoice\CreditNote\InvoiceCreditNoteMessageFactory;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;
use Ozean12\Transfer\Message\CreditNote\CreateCreditNote;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;

/**
 * @TODO: add the scenarios for normal and shipped order cancellations
 */
class LegacyCancelOrderUseCaseSpec extends ObjectBehavior
{
    private const ORDER_UUID = 'test-order-uuid';

    private const ORDER_ID = 567;

    private const MERCHANT_ID = 14;

    public function let(
        MerchantDebtorLimitsService $limitsService,
        OrderContainerFactory $orderContainerFactory,
        MerchantRepository $merchantRepository,
        Registry $workflowRegistry,
        InvoiceCreditNoteMessageFactory $creditNoteMessageFactory,
        CreditNoteFactory $creditNoteFactory,
        MessageBusInterface $bus,
        Workflow $workflow,
        LoggerInterface $logger,
        CancelOrderRequest $request,
        OrderContainer $orderContainer,
        OrderEntity $order,
        CreateCreditNote $creditNoteMessage
    ) {
        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger);

        $order->isWorkflowV2()->willReturn(false);
        $request->getOrderId()->willReturn(self::ORDER_UUID);
        $request->getMerchantId()->willReturn(self::MERCHANT_ID);

        $workflowRegistry->get($order)->willReturn($workflow);

        $creditNoteFactory->create(Argument::cetera())->willReturn($this->createCreditNote());
        $creditNoteMessageFactory->create(Argument::cetera())->willReturn($creditNoteMessage);

        $orderContainer->getOrder()->willReturn($order);
        $order->getId()->willReturn(self::ORDER_ID);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(LegacyCancelOrderUseCase::class);
    }

    public function it_throws_exception_if_order_does_not_exist(
        OrderContainerFactory $orderContainerFactory,
        CancelOrderRequest $request
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(self::MERCHANT_ID, self::ORDER_UUID)
            ->shouldBeCalled()
            ->willThrow(OrderContainerFactoryException::class);

        $this->shouldThrow(OrderNotFoundException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_order_is_in_wrong_state(
        MerchantDebtorLimitsService $limitsService,
        OrderContainerFactory $orderContainerFactory,
        CancelOrderRequest $request,
        Workflow $workflow,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(self::MERCHANT_ID, self::ORDER_UUID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $workflow
            ->can($order, Argument::any())
            ->shouldBeCalled()
            ->willReturn(false);

        $limitsService->unlock($orderContainer)->shouldNotBeCalled();

        $this->shouldThrow(CancelOrderException::class)->during('execute', [$request]);
    }

    public function it_cancels_waiting_order_state(
        MerchantDebtorLimitsService $limitsService,
        OrderContainerFactory $orderContainerFactory,
        CancelOrderRequest $request,
        Workflow $workflow,
        InvoiceCreditNoteMessageFactory $creditNoteMessageFactory,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(self::MERCHANT_ID, self::ORDER_UUID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $workflow
            ->can($order, 'cancel')
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $workflow
            ->can($order, 'cancel_shipped')
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $workflow
            ->can($order, 'cancel_waiting')
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $limitsService->unlock($orderContainer)->shouldNotBeCalled();
        $creditNoteMessageFactory->create(Argument::cetera())->shouldNotBeCalled();
        $workflow->apply($order, OrderEntity::TRANSITION_CANCEL_WAITING)->shouldBeCalledOnce();

        $this->execute($request);
    }

    public function it_cancels_shipped_order_state(
        MerchantDebtorLimitsService $limitsService,
        OrderContainerFactory $orderContainerFactory,
        CancelOrderRequest $request,
        Workflow $workflow,
        InvoiceCreditNoteMessageFactory $creditNoteMessageFactory,
        OrderEntity $order,
        OrderContainer $orderContainer,
        Invoice $invoice,
        OrderFinancialDetailsEntity $orderFinancialDetails,
        InvoiceCollection $invoiceCollection,
        MessageBusInterface $bus
    ) {
        $invoiceCollection->getInvoicesCreditNotesGrossSum()->willReturn(new Money(200));
        $invoiceCollection->getInvoicesCreditNotesNetSum()->willReturn(new Money(150));
        $orderContainer->getOrder()->willReturn($order);
        $invoiceCollection->getLastInvoice()->willReturn($invoice);
        $orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(self::MERCHANT_ID, self::ORDER_UUID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $workflow
            ->can($order, 'cancel')
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $workflow
            ->can($order, 'cancel_shipped')
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $orderFinancialDetails->getAmountGross()->willReturn(new Money());
        $orderFinancialDetails->getAmountNet()->willReturn(new Money());
        $orderFinancialDetails->getAmountTax()->willReturn(new Money());

        $invoice->getExternalCode()->willReturn('CORE');
        $invoice->getCreditNotes()->willReturn(new CreditNoteCollection([]));
        $invoiceCollection->isEmpty()->willReturn(false);
        $orderContainer->getInvoices()->willReturn($invoiceCollection);

        $limitsService->unlock($orderContainer)->shouldNotBeCalled();
        $orderContainer->getOrderFinancialDetails()->willReturn($orderFinancialDetails);
        $creditNoteMessageFactory->create(Argument::cetera())->shouldBeCalled();
        $workflow->apply($order, OrderEntity::TRANSITION_CANCEL_SHIPPED)->shouldBeCalledOnce();

        $bus->dispatch(Argument::any())->shouldBeCalled()->willReturn(new Envelope(new CreateCreditNote()));

        $this->execute($request);
    }

    private function createCreditNote(): CreditNote
    {
        return (new CreditNote())
            ->setUuid(Uuid::uuid4()->toString())
            ->setAmount(TaxedMoneyFactory::create(123, 22, 11))
            ->setExternalCode('CORE-CN')
            ->setInvoiceUuid(self::ORDER_UUID);
    }
}
