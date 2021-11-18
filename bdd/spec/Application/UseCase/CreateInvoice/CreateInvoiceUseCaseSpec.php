<?php

namespace spec\App\Application\UseCase\CreateInvoice;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\CreateInvoice\CreateInvoiceRequest;
use App\Application\UseCase\CreateInvoice\CreateInvoiceUseCase;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceFactory;
use App\DomainModel\Invoice\ShippingInfo\ShippingInfoRepository;
use App\DomainModel\Order\Lifecycle\ShipOrder\ShipOrderInterface;
use App\DomainModel\Order\Lifecycle\ShipOrder\ShipOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\InvoiceDocumentUploadHandlerAggregator;
use App\DomainModel\OrderResponse\LegacyOrderResponse;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;

class CreateInvoiceUseCaseSpec extends ObjectBehavior
{
    private const ID = 'uuidAAABBB';

    private const MERCHANT_ID = 50;

    private const EXTERNAL_CODE = 'test-code';

    private const INVOICE_NUMBER = 'DE156893';

    private const INVOICE_URL = 'https://invoice.com/test.pdf';

    private const PROOF_OF_DELIVERY_URL = 'https://invoice.com/proof.pdf';

    private const PAYMENT_DETAILS_ID = '4d5w45d4';

    public function let(
        InvoiceDocumentUploadHandlerAggregator $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        Registry $workflowRegistry,
        ShipOrderService $shipOrderService,
        InvoiceFactory $invoiceFactory,
        ShippingInfoRepository $shippingInfoRepository,
        OrderContainer $orderContainer,
        OrderEntity $order,
        LegacyOrderResponse $orderResponse,
        CreateInvoiceRequest $request,
        ValidatorInterface $validator,
        Workflow $workflow,
        LoggerInterface $logger
    ) {
        $order->isWorkflowV1()->willReturn(false);
        $orderContainer->getOrder()->willReturn($order);

        $request->getOrders()->willReturn([self::ID]);
        $request->getMerchantId()->willReturn(self::MERCHANT_ID);
        $request->getExternalCode()->willReturn(self::EXTERNAL_CODE);
        $request->getExternalCode()->willReturn(self::INVOICE_NUMBER);
        $request->getInvoiceUrl()->willReturn(self::INVOICE_URL);

        $orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(Argument::any(), Argument::any())
            ->willReturn($orderContainer);

        $workflowRegistry->get($order)->willReturn($workflow);
        $validator->validate(Argument::cetera())->willReturn(new ConstraintViolationList([]));

        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger);
        $this->setValidator($validator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CreateInvoiceUseCase::class);
    }

    public function it_ships_order_if_already_has_payment_details_and_can_ship(
        OrderContainerFactory $orderContainerFactory,
        CreateInvoiceRequest $request,
        OrderContainer $orderContainer,
        OrderEntity $order,
        ShipOrderInterface $shipOrderService,
        Workflow $workflow,
        OrderFinancialDetailsEntity $financialDetailsEntity,
        InvoiceFactory $invoiceFactory,
        TaxedMoney $shipAmount,
        Invoice $invoice
    ) {
        $financialDetailsEntity->getDuration()->willReturn(30);
        $orderContainerFactory->loadByMerchantIdAndUuid(Argument::cetera())->willReturn($orderContainer);
        $orderContainer->getOrderFinancialDetails()->willReturn($financialDetailsEntity);

        $shipAmount->getGross()->willReturn(new Money(500));
        $shipAmount->getNet()->willReturn(new Money(450));
        $shipAmount->getTax()->willReturn(new Money(50));

        $request->getAmount()->willReturn($shipAmount);
        $request->getExternalCode()->willReturn(self::INVOICE_NUMBER);
        $request->getShippingDocumentUrl()->willReturn(self::PROOF_OF_DELIVERY_URL);
        $request->getInvoiceUrl()->willReturn(self::PROOF_OF_DELIVERY_URL);

        $financialDetailsEntity->getUnshippedAmountGross()->willReturn(new Money(1000));
        $financialDetailsEntity->getUnshippedAmountNet()->willReturn(new Money(1100));
        $financialDetailsEntity->getUnshippedAmountTax()->willReturn(new Money(100));

        $invoiceFactory->create(
            $orderContainer,
            $request
        )->willReturn($invoice);

        $order->getUuid()->willReturn('1768ab4c-8cab-4166-a892-6bd3efe9ec13');
        $order->getState()->willReturn(OrderEntity::STATE_CREATED);
        $order->getExternalCode()->willReturn(self::EXTERNAL_CODE);
        $order->getPaymentId()->willReturn(self::PAYMENT_DETAILS_ID);

        $invoice->getUuid()->willReturn('3c5593cb-04a6-41e6-b57b-1d65dd98e252');

        $workflow->can($order, OrderEntity::TRANSITION_SHIP_FULLY)->shouldBeCalled()->willReturn(true);
        $shipOrderService->ship($orderContainer, $invoice)->shouldBeCalled();

        $this->execute($request);
    }

    public function it_fails_if_order_cannot_transition_to_shipped(
        OrderContainerFactory $orderContainerFactory,
        CreateInvoiceRequest $request,
        OrderContainer $orderContainer,
        OrderEntity $order,
        ShipOrderInterface $shipOrderService,
        Workflow $workflow,
        Invoice $invoice
    ) {
        $orderContainerFactory->loadByMerchantIdAndUuid(Argument::cetera())->willReturn($orderContainer);

        $order->getState()->willReturn(OrderEntity::STATE_CANCELED);

        $order->getExternalCode()->shouldBeCalled()->willReturn('some_code');
        $workflow->can(Argument::cetera())->shouldBeCalled()->willReturn(false);
        $shipOrderService->ship($orderContainer, $invoice)->shouldNotBeCalled();

        $this->shouldThrow(WorkflowException::class)->during('execute', [$request]);
    }
}
