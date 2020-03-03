<?php

namespace spec\App\Application\UseCase\ShipOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ShipOrder\ShipOrderRequest;
use App\Application\UseCase\ShipOrder\ShipOrderUseCase;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use App\DomainModel\Payment\OrderPaymentDetailsDTO;
use App\DomainModel\Payment\PaymentRequestFactory;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Payment\PaymentsServiceRequestException;
use App\DomainModel\Payment\RequestDTO\CreateRequestDTO;
use App\Helper\Uuid\UuidGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
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
        Workflow $orderWorkflow,
        OrderRepositoryInterface $orderRepository,
        PaymentsServiceInterface $paymentsService,
        OrderInvoiceManager $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        OrderResponseFactory $orderResponseFactory,
        PaymentRequestFactory $paymentRequestFactory,
        UuidGenerator $uuidGenerator,
        OrderStateManager $orderStateManager,
        ValidatorInterface $validator,
        OrderContainer $orderContainer,
        OrderEntity $order,
        MerchantDebtorEntity $merchantDebtor,
        ShipOrderRequest $request
    ) {
        $validator->validate(Argument::any(), Argument::any(), Argument::any())->willReturn(new ConstraintViolationList());
        $orderContainer->getOrder()->willReturn($order);
        $orderContainer->getMerchantDebtor()->willReturn($merchantDebtor);

        $paymentRequestFactory->createCreateRequestDTO(Argument::any())->willReturn(new CreateRequestDTO());

        $request->getOrderId()->willReturn(self::ID);
        $request->getMerchantId()->willReturn(self::MERCHANT_ID);
        $request->getExternalCode()->willReturn(self::EXTERNAL_CODE);
        $request->getInvoiceNumber()->willReturn(self::INVOICE_NUMBER);
        $request->getInvoiceUrl()->willReturn(self::INVOICE_URL);
        $request->getShippingDocumentUrl()->willReturn(self::PROOF_OF_DELIVERY_URL);
        $orderResponseFactory->create(Argument::type(OrderContainer::class))
            ->willReturn(new OrderResponse());

        $this->beConstructedWith(...func_get_args());

        $this->setValidator($validator);
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
        Workflow $orderWorkflow,
        PaymentsServiceInterface $paymentsService,
        OrderContainerFactory $orderContainerFactory,
        ShipOrderRequest $request,
        OrderContainer $orderContainer,
        OrderEntity $order,
        OrderPaymentDetailsDTO $paymentDetailsDTO
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(self::MERCHANT_ID, self::ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $order->getState()->willReturn(OrderStateManager::STATE_CREATED);
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
        $order->setShippedAt(Argument::type(\DateTime::class))
            ->shouldBeCalled()
            ->willReturn($order);

        $paymentsService
            ->getOrderPaymentDetails(self::PAYMENT_DETAILS_ID)
            ->shouldBeCalled()
            ->willReturn($paymentDetailsDTO);
        $orderContainer
            ->setPaymentDetails(Argument::type(OrderPaymentDetailsDTO::class))
            ->shouldBeCalled();
        $orderWorkflow->can($order, OrderStateManager::TRANSITION_SHIP)
            ->shouldBeCalledOnce()->willReturn(true);

        $this->execute($request);
    }

    public function it_fails_if_order_cannot_transition_to_shipped(
        Workflow $orderWorkflow,
        PaymentsServiceInterface $paymentsService,
        OrderContainerFactory $orderContainerFactory,
        ShipOrderRequest $request,
        OrderContainer $orderContainer,
        OrderStateManager $orderStateManager,
        OrderEntity $order
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(self::MERCHANT_ID, self::ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $order->getState()->willReturn(OrderStateManager::STATE_CANCELED);
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
        $order->setShippedAt(Argument::type(\DateTime::class))
            ->shouldBeCalled()
            ->willReturn($order);

        $orderStateManager->ship($orderContainer)->shouldNotBeCalled();

        $paymentsService
            ->getOrderPaymentDetails(self::PAYMENT_DETAILS_ID)
            ->shouldBeCalled()
            ->willThrow(PaymentsServiceRequestException::class);
        $orderContainer
            ->setPaymentDetails(Argument::type(OrderPaymentDetailsDTO::class))
            ->shouldNotBeCalled();
        $orderWorkflow->can($order, OrderStateManager::TRANSITION_SHIP)
            ->shouldBeCalledOnce()->willReturn(false);

        $this->shouldThrow(WorkflowException::class)->during('execute', [$request]);
    }

    public function it_updates_order_and_create_borscht_order(
        Workflow $orderWorkflow,
        PaymentsServiceInterface $paymentsService,
        OrderContainerFactory $orderContainerFactory,
        ShipOrderRequest $request,
        OrderContainer $orderContainer,
        OrderEntity $order,
        OrderStateManager $orderStateManager
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(self::MERCHANT_ID, self::ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $order->getState()->willReturn(OrderStateManager::STATE_CREATED);
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
        $order->setShippedAt(Argument::type(\DateTime::class))
            ->shouldBeCalled()
            ->willReturn($order);

        $paymentsService
            ->getOrderPaymentDetails(self::PAYMENT_DETAILS_ID)
            ->shouldBeCalled()
            ->willThrow(PaymentsServiceRequestException::class);
        $orderWorkflow->can($order, OrderStateManager::TRANSITION_SHIP)
            ->shouldBeCalledOnce()->willReturn(true);

        $paymentsService->createOrder(Argument::type(CreateRequestDTO::class))
            ->shouldBeCalled();

        $orderContainer
            ->setPaymentDetails(Argument::type(OrderPaymentDetailsDTO::class))
            ->shouldBeCalled();

        $orderStateManager->ship($orderContainer)->shouldBeCalled();

        $this->execute($request);
    }
}
