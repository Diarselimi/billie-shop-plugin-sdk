<?php

namespace spec\App\Application\UseCase\ShipOrder;

use App\Application\PaellaCoreCriticalException;
use App\Application\UseCase\ShipOrder\ShipOrderRequest;
use App\Application\UseCase\ShipOrder\ShipOrderUseCase;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Borscht\OrderPaymentDetailsDTO;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
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
    private const EXTERNAL_CODE = 'test-code';

    private const MERCHANT_ID = 1;

    private const INVOICE_NUMBER = 'DE156893';

    private const INVOICE_URL = 'https://invoice.com/test.pdf';

    private const PROOF_OF_DELIVERY_URL = 'https://invoice.com/proof.pdf';

    private const MERCHANT_DEBTOR_ID = 10;

    private const PAYMENT_DEBTOR_ID = '4d5w45d4-4d5w45d4-4d5w45d4-4d5w45d4';

    private const PAYMENT_DETAILS_ID = '4d5w45d4';

    public function let(
        Workflow $workflow,
        OrderRepositoryInterface $orderRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        BorschtInterface $paymentsService,
        OrderInvoiceManager $invoiceManager,
        OrderPersistenceService $orderPersistenceService,
        OrderResponseFactory $orderResponseFactory,
        ValidatorInterface $validator
    ) {
        $validator->validate(Argument::any(), Argument::any(), Argument::any())->willReturn(new ConstraintViolationList());

        $this->beConstructedWith(
            $workflow,
            $orderRepository,
            $merchantDebtorRepository,
            $paymentsService,
            $invoiceManager,
            $orderPersistenceService,
            $orderResponseFactory
        );

        $this->setValidator($validator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ShipOrderUseCase::class);
    }

    public function it_throws_exception_if_order_was_not_found(OrderRepositoryInterface $orderRepository)
    {
        $request = $this->mockRequest();

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::EXTERNAL_CODE, self::MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->shouldThrow(PaellaCoreCriticalException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_order_was_not_in_state_for_shipping(
        Workflow $workflow,
        OrderRepositoryInterface $orderRepository,
        OrderEntity $order
    ) {
        $request = $this->mockRequest();

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::EXTERNAL_CODE, self::MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order)
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
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        BorschtInterface $paymentsService,
        OrderEntity $order,
        MerchantDebtorEntity $merchantDebtor,
        OrderPaymentDetailsDTO $paymentDetailsDTO,
        OrderInvoiceManager $invoiceManager,
        OrderPersistenceService $orderPersistenceService,
        OrderResponseFactory $orderResponseFactory
    ) {
        $order->getMerchantDebtorId()->willReturn(self::MERCHANT_DEBTOR_ID);
        $order->getExternalCode()->willReturn("test");

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::EXTERNAL_CODE, self::MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order)
        ;

        $orderPersistenceService->createFromOrderEntity($order)->willReturn(new OrderContainer());
        $orderResponseFactory->create(new OrderContainer())->willReturn(new OrderResponse());
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

        $merchantDebtorRepository
            ->getOneById(self::MERCHANT_DEBTOR_ID)
            ->shouldBeCalled()
            ->willReturn($merchantDebtor)
        ;

        $paymentDetailsDTO->getId()->willReturn(self::PAYMENT_DETAILS_ID);

        $paymentsService
            ->createOrder($order, self::PAYMENT_DEBTOR_ID)
            ->shouldBeCalled()
            ->willReturn($paymentDetailsDTO)
        ;

        $order->setPaymentId(self::PAYMENT_DETAILS_ID)->shouldBeCalled();

        $workflow->apply($order, OrderStateManager::TRANSITION_SHIP)->shouldBeCalled();

        $invoiceManager->upload($order, 'order.shipment')->shouldBeCalledOnce();
        $orderRepository->update($order)->shouldBeCalled();

        $request = $this->mockRequest();

        $this->execute($request);
    }

    private function mockRequest(): ShipOrderRequest
    {
        return (new ShipOrderRequest())
            ->setOrderId(self::EXTERNAL_CODE)
            ->setMerchantId(self::MERCHANT_ID)
            ->setInvoiceNumber(self::INVOICE_NUMBER)
            ->setInvoiceUrl(self::INVOICE_URL)
            ->setProofOfDeliveryUrl(self::PROOF_OF_DELIVERY_URL)
        ;
    }
}
