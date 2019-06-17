<?php

namespace spec\App\Application\UseCase\ShipOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\PaellaCoreCriticalException;
use App\Application\UseCase\ShipOrder\ShipOrderRequest;
use App\Application\UseCase\ShipOrder\ShipOrderUseCase;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Borscht\OrderPaymentDetailsDTO;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
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

    private const MERCHANT_DEBTOR_ID = 10;

    private const PAYMENT_DEBTOR_ID = '4d5w45d4-4d5w45d4-4d5w45d4-4d5w45d4';

    private const PAYMENT_DETAILS_ID = '4d5w45d4';

    private const DURATION = 25;

    private const AMOUNT_GROSS = 1500.25;

    public function let(
        Workflow $workflow,
        OrderRepositoryInterface $orderRepository,
        BorschtInterface $paymentsService,
        OrderInvoiceManager $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        OrderResponseFactory $orderResponseFactory,
        ValidatorInterface $validator,
        OrderContainer $orderContainer,
        OrderEntity $order,
        MerchantDebtorEntity $merchantDebtor,
        ShipOrderRequest $request
    ) {
        $validator->validate(Argument::any(), Argument::any(), Argument::any())->willReturn(new ConstraintViolationList());
        $orderContainer->getOrder()->willReturn($order);
        $orderContainer->getMerchantDebtor()->willReturn($merchantDebtor);

        $request->getOrderId()->willReturn(self::ID);
        $request->getMerchantId()->willReturn(self::MERCHANT_ID);
        $request->getExternalCode()->willReturn(self::EXTERNAL_CODE);
        $request->getInvoiceNumber()->willReturn(self::INVOICE_NUMBER);
        $request->getInvoiceUrl()->willReturn(self::INVOICE_URL);
        $request->getProofOfDeliveryUrl()->willReturn(self::PROOF_OF_DELIVERY_URL);

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
            ->loadByMerchantIdAndExternalId(self::MERCHANT_ID, self::ID)
            ->shouldBeCalled()
            ->willThrow(OrderContainerFactoryException::class)
        ;

        $this->shouldThrow(OrderNotFoundException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_order_was_not_in_state_for_shipping(
        Workflow $workflow,
        OrderContainerFactory $orderContainerFactory,
        ShipOrderRequest $request,
        OrderContainer $orderContainer,
        OrderEntity $order
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalId(self::MERCHANT_ID, self::ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $workflow
            ->can($order, OrderStateManager::TRANSITION_SHIP)
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_updates_order_and_create_borscht_order(
        Workflow $workflow,
        OrderRepositoryInterface $orderRepository,
        BorschtInterface $paymentsService,
        OrderInvoiceManager $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        OrderResponseFactory $orderResponseFactory,
        MerchantDebtorEntity $merchantDebtor,
        OrderPaymentDetailsDTO $paymentDetailsDTO,
        ShipOrderRequest $request,
        OrderContainer $orderContainer,
        OrderEntity $order,
        OrderResponse $orderResponse,
        OrderFinancialDetailsEntity $orderFinancialDetails
    ) {
        $order->getMerchantDebtorId()->willReturn(self::MERCHANT_DEBTOR_ID);
        $order->getExternalCode()->willReturn(self::EXTERNAL_CODE);
        $order->getInvoiceNumber()->willReturn(self::INVOICE_NUMBER);
        $order->getShippedAt()->willReturn(new \DateTime());
        $orderContainer->getOrder()->willReturn($order);

        $orderFinancialDetails->getDuration()->willReturn(self::DURATION);
        $orderFinancialDetails->getAmountGross()->willReturn(self::AMOUNT_GROSS);
        $orderContainer->getOrderFinancialDetails()->willReturn($orderFinancialDetails);

        $merchantDebtor->getPaymentDebtorId()->shouldBeCalled()->willReturn(self::PAYMENT_DEBTOR_ID);
        $orderContainerFactory
            ->loadByMerchantIdAndExternalId(self::MERCHANT_ID, self::ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $orderResponseFactory->create($orderContainer)->willReturn($orderResponse);
        $workflow
            ->can($order, OrderStateManager::TRANSITION_SHIP)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $order->setInvoiceNumber(self::INVOICE_NUMBER)->shouldBeCalled()->willReturn($order);
        $order->setInvoiceUrl(self::INVOICE_URL)->shouldBeCalled()->willReturn($order);
        $order->setProofOfDeliveryUrl(self::PROOF_OF_DELIVERY_URL)->shouldBeCalled()->willReturn($order);
        $order->setShippedAt(Argument::type(\DateTime::class))->shouldBeCalled()->willReturn($order);

        $merchantDebtor->getPaymentDebtorId()->willReturn(self::PAYMENT_DEBTOR_ID);

        $paymentDetailsDTO->getId()->willReturn(self::PAYMENT_DETAILS_ID);

        $paymentsService
            ->createOrder(
                self::PAYMENT_DEBTOR_ID,
                self::INVOICE_NUMBER,
                Argument::type(\DateTime::class),
                self::DURATION,
                self::AMOUNT_GROSS,
                self::EXTERNAL_CODE
            )
            ->shouldBeCalled()
            ->willReturn($paymentDetailsDTO)
        ;

        $order->setPaymentId(self::PAYMENT_DETAILS_ID)->shouldBeCalled();

        $workflow->apply($order, OrderStateManager::TRANSITION_SHIP)->shouldBeCalled();

        $invoiceManager->upload($order, 'order.shipment')->shouldBeCalledOnce();
        $orderRepository->update($order)->shouldBeCalled();

        $orderResponseFactory->create($orderContainer)->shouldBeCalled();

        $this->execute($request);
    }
}
