<?php

namespace spec\App\Application\UseCase\ShipOrder;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ShipOrder\ShipOrderRequest;
use App\Application\UseCase\ShipOrder\ShipOrderRequestV1;
use App\Application\UseCase\ShipOrder\ShipOrderUseCase;
use App\DomainModel\FeatureFlag\FeatureFlagManager;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceFactory;
use App\DomainModel\Order\Lifecycle\ShipOrder\ShipOrderInterface;
use App\DomainModel\Order\Lifecycle\ShipOrder\ShipOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use App\Infrastructure\Repository\OrderRepository;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;

class ShipOrderUseCaseSpec extends ObjectBehavior
{
    private const ID = 'uuidAAABBB';

    private const MERCHANT_ID = 50;

    private const EXTERNAL_CODE = 'test-code';

    private const INVOICE_NUMBER = 'DE156893';

    private const INVOICE_URL = 'https://invoice.com/test.pdf';

    private const PROOF_OF_DELIVERY_URL = 'https://invoice.com/proof.pdf';

    private const PAYMENT_DETAILS_ID = '4d5w45d4';

    public function let(
        OrderInvoiceManager $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        Registry $workflowRegistry,
        ShipOrderService $shipOrderService,
        OrderResponseFactory $orderResponseFactory,
        FeatureFlagManager $featureFlagManager,
        InvoiceFactory $invoiceFactory,
        OrderContainer $orderContainer,
        OrderEntity $order,
        OrderResponse $orderResponse,
        ShipOrderRequestV1 $request,
        ValidatorInterface $validator,
        Workflow $workflow,
        LoggerInterface $logger
    ) {
        $order->isWorkflowV1()->willReturn(false);
        $featureFlagManager->isEnabled(FeatureFlagManager::FEATURE_INVOICE_BUTLER)->willReturn(true);
        $orderContainer->getOrder()->willReturn($order);

        $request->getOrderId()->willReturn(self::ID);
        $request->getMerchantId()->willReturn(self::MERCHANT_ID);
        $request->getExternalCode()->willReturn(self::EXTERNAL_CODE);
        $request->getInvoiceNumber()->willReturn(self::INVOICE_NUMBER);
        $request->getInvoiceUrl()->willReturn(self::INVOICE_URL);
        $request->getShippingDocumentUrl()->willReturn(self::PROOF_OF_DELIVERY_URL);

        $orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(Argument::any(), Argument::any())
            ->willReturn($orderContainer);

        $workflowRegistry->get($order)->willReturn($workflow);
        $validator->validate(Argument::cetera())->willReturn(new ConstraintViolationList([]));
        $orderResponseFactory->create($orderContainer)->willReturn($orderResponse);

        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger);
        $this->setValidator($validator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ShipOrderUseCase::class);
    }

    public function it_ships_order_if_already_has_payment_details_and_can_ship(
        OrderContainerFactory $orderContainerFactory,
        ShipOrderRequest $request,
        OrderContainer $orderContainer,
        OrderEntity $order,
        ShipOrderInterface $shipOrderService,
        Workflow $workflow,
        OrderFinancialDetailsEntity $financialDetailsEntity,
        InvoiceFactory $invoiceFactory,
        TaxedMoney $shipAmount,
        Invoice $invoice
    ) {
        $orderContainerFactory->loadByMerchantIdAndUuid(Argument::cetera())->willReturn($orderContainer);
        $orderContainer->getOrderFinancialDetails()->willReturn($financialDetailsEntity);

        $shipAmount->getGross()->willReturn(new Money(500));
        $shipAmount->getNet()->willReturn(new Money(450));
        $shipAmount->getTax()->willReturn(new Money(50));

        $request->getAmount()->willReturn($shipAmount);
        $request->getDuration()->willReturn(30);

        $financialDetailsEntity->getUnshippedAmountGross()->willReturn(new Money(1000));
        $financialDetailsEntity->getUnshippedAmountNet()->willReturn(new Money(1100));
        $financialDetailsEntity->getUnshippedAmountTax()->willReturn(new Money(100));

        $invoiceFactory->create(
            $orderContainer,
            $shipAmount,
            30,
            self::INVOICE_NUMBER,
            self::PROOF_OF_DELIVERY_URL
        )->willReturn($invoice);

        $order->getState()->willReturn(OrderEntity::STATE_CREATED);
        $order->getExternalCode()->willReturn(self::EXTERNAL_CODE);
        $order->getPaymentId()->willReturn(self::PAYMENT_DETAILS_ID);

        $workflow->can($order, OrderEntity::TRANSITION_SHIP_FULLY)->shouldBeCalled()->willReturn(true);
        $shipOrderService->ship($orderContainer, $invoice)->shouldBeCalled();

        $this->execute($request);
    }

    public function it_fails_if_order_cannot_transition_to_shipped(
        OrderContainerFactory $orderContainerFactory,
        ShipOrderRequest $request,
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
