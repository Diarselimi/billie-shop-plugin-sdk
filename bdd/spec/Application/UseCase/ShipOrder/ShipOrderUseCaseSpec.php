<?php

namespace spec\App\Application\UseCase\ShipOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ShipOrder\ShipOrderRequest;
use App\Application\UseCase\ShipOrder\ShipOrderUseCase;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use App\DomainModel\ShipOrder\ShipOrderService;
use App\Helper\Uuid\UuidGeneratorInterface;
use PhpSpec\ObjectBehavior;

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
        UuidGeneratorInterface $uuidGenerator,
        ShipOrderService $shipOrderService,
        OrderContainer $orderContainer,
        OrderEntity $order,
        ShipOrderRequest $request
    ) {
        $orderContainer->getOrder()->willReturn($order);

        $request->getOrderId()->willReturn(self::ID);
        $request->getMerchantId()->willReturn(self::MERCHANT_ID);
        $request->getExternalCode()->willReturn(self::EXTERNAL_CODE);
        $request->getInvoiceNumber()->willReturn(self::INVOICE_NUMBER);
        $request->getInvoiceUrl()->willReturn(self::INVOICE_URL);
        $request->getShippingDocumentUrl()->willReturn(self::PROOF_OF_DELIVERY_URL);

        $this->beConstructedWith(...func_get_args());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ShipOrderUseCase::class);
    }

    public function it_throws_exception_if_order_was_not_found(
        OrderContainerFactory $orderContainerFactory,
        ShipOrderRequest $request
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(self::MERCHANT_ID, self::ID)
            ->shouldBeCalled()
            ->willThrow(OrderContainerFactoryException::class);

        $this->shouldThrow(OrderNotFoundException::class)->during('execute', [$request]);
    }

    public function it_ships_order_if_already_has_payment_details_and_can_ship(
        OrderContainerFactory $orderContainerFactory,
        ShipOrderRequest $request,
        OrderContainer $orderContainer,
        OrderEntity $order,
        ShipOrderService $shipOrderService
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(self::MERCHANT_ID, self::ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $order->getState()->willReturn(OrderEntity::STATE_CREATED);
        $order->getExternalCode()->shouldBeCalled()->willReturn(self::EXTERNAL_CODE);
        $order->getPaymentId()->shouldBeCalled()->willReturn(self::PAYMENT_DETAILS_ID);
        $order->setInvoiceNumber(self::INVOICE_NUMBER)
            ->shouldBeCalled()
            ->willReturn($order);
        $order->setInvoiceUrl(self::INVOICE_URL)
            ->shouldBeCalled()
            ->willReturn($order);
        $order->setProofOfDeliveryUrl(self::PROOF_OF_DELIVERY_URL)
            ->shouldBeCalled()
            ->willReturn($order);

        $shipOrderService->validate($request, $order)->shouldBeCalledOnce();
        $shipOrderService->hasPaymentDetails($orderContainer)->willReturn(true);

        $shipOrderService->shipOrder($orderContainer, true)->shouldBeCalled();

        $this->execute($request);
    }

    public function it_fails_if_order_cannot_transition_to_shipped(
        OrderContainerFactory $orderContainerFactory,
        ShipOrderRequest $request,
        OrderContainer $orderContainer,
        OrderEntity $order,
        ShipOrderService $shipOrderService
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(self::MERCHANT_ID, self::ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $order->getState()->willReturn(OrderEntity::STATE_CANCELED);

        $shipOrderService->validate($request, $order)->shouldBeCalledOnce()->willThrow(WorkflowException::class);

        $shipOrderService->shipOrder($orderContainer, false)->shouldNotBeCalled();

        $this->shouldThrow(WorkflowException::class)->during('execute', [$request]);
    }

    public function it_updates_order_and_create_borscht_order(
        OrderContainerFactory $orderContainerFactory,
        ShipOrderRequest $request,
        OrderContainer $orderContainer,
        OrderEntity $order,
        ShipOrderService $shipOrderService
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(self::MERCHANT_ID, self::ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $order->getState()->willReturn(OrderEntity::STATE_CREATED);
        $order->getExternalCode()->shouldBeCalled()->willReturn(self::EXTERNAL_CODE);
        $order->getPaymentId()->shouldBeCalled()->willReturn(self::PAYMENT_DETAILS_ID);
        $order->setInvoiceNumber(self::INVOICE_NUMBER)
            ->shouldBeCalled()
            ->willReturn($order);
        $order->setInvoiceUrl(self::INVOICE_URL)
            ->shouldBeCalled()
            ->willReturn($order);
        $order->setProofOfDeliveryUrl(self::PROOF_OF_DELIVERY_URL)
            ->shouldBeCalled()
            ->willReturn($order);

        $shipOrderService->validate($request, $order)->shouldBeCalledOnce();

        $shipOrderService->hasPaymentDetails($orderContainer)->willReturn(false);

        $shipOrderService->shipOrder($orderContainer, false)->shouldBeCalled();

        $this->execute($request);
    }
}
