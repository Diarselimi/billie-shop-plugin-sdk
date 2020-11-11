<?php

namespace spec\App\Application\UseCase\MarkOrderAsFraud;

use App\Application\Exception\FraudOrderException;
use App\Application\UseCase\MarkOrderAsFraud\FraudReclaimActionException;
use App\Application\UseCase\MarkOrderAsFraud\MarkOrderAsFraudRequest;
use App\Application\UseCase\MarkOrderAsFraud\MarkOrderAsFraudUseCase;
use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use Ozean12\Money\Money;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MarkOrderAsFraudUseCaseSpec extends ObjectBehavior
{
    private const ORDER_UUID = 'test';

    private const ORDER_PAYMENT_ID = 'DIDI';

    private const DELIVERY_ADDRESS_CITY = 'Berlin';

    private const DELIVERY_ADDRESS_POSTAL_CODE = '10999';

    private const DELIVERY_ADDRESS_STREET = 'testStr';

    private const DELIVERY_ADDRESS_HOUSE_NUMBER = '11';

    private const DEBTOR_ADDRESS_CITY = 'Hanover';

    private const DEBTOR_ADDRESS_POSTAL_CODE = '10999';

    private const DEBTOR_ADDRESS_STREET = 'testStr';

    private const DEBTOR_ADDRESS_HOUSE_NUMBER = '11';

    private const ORDER_FINANCIAL_DETAILS_AMOUNT_GROSS = '1000';

    public function let(
        OrderRepositoryInterface $orderRepository,
        OrderContainerFactory $orderContainerFactory,
        PaymentsServiceInterface $borscht,
        MarkOrderAsFraudRequest $request,
        OrderContainer $orderContainer,
        OrderEntity $order,
        AddressEntity $deliveryAddress,
        AddressEntity $debtorAddress,
        DebtorExternalDataEntity $debtorExternalData,
        OrderFinancialDetailsEntity $orderFinancialDetails
    ) {
        $request->getUuid()->willReturn(self::ORDER_UUID);

        $this->mockOrderData($orderContainer, $order, $debtorAddress, $deliveryAddress, $debtorExternalData, $orderFinancialDetails);

        $this->beConstructedWith(...func_get_args());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MarkOrderAsFraudUseCase::class);
    }

    public function it_throws_exception_if_order_was_not_found(
        OrderContainerFactory $orderContainerFactory,
        MarkOrderAsFraudRequest $request
    ) {
        $orderContainerFactory
            ->loadByUuid(self::ORDER_UUID)
            ->shouldBeCalled()
            ->willThrow(OrderContainerFactoryException::class)
        ;

        $this->shouldThrow(OrderNotFoundException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_order_was_already_marked_as_fraud(
        OrderContainerFactory $orderContainerFactory,
        MarkOrderAsFraudRequest $request,
        OrderContainer $orderContainer,
        OrderEntity $order
    ) {
        $order->getMarkedAsFraudAt()->willReturn(new \DateTime());

        $orderContainerFactory
            ->loadByUuid(self::ORDER_UUID)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $this->shouldThrow(FraudOrderException::class)->during('execute', [$request]);
    }

    public function it_sets_marked_as_fraud_at_date_to_current_date_time_and_call_borscht_to_create_fraud_reclaim(
        MarkOrderAsFraudRequest $request,
        OrderRepositoryInterface $orderRepository,
        OrderContainerFactory $orderContainerFactory,
        PaymentsServiceInterface $borscht,
        DebtorExternalDataEntity $debtorExternalData,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $orderContainerFactory
            ->loadByUuid(self::ORDER_UUID)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $order->isLate()->willReturn(true);
        $order->isPaidOut()->willReturn(true);

        $order->setMarkedAsFraudAt(Argument::type(\DateTime::class))->shouldBeCalled();
        $orderRepository->update($order)->shouldBeCalled();

        $debtorExternalData->isEstablishedCustomer()->willReturn(null);

        $borscht->createFraudReclaim(self::ORDER_PAYMENT_ID)->shouldBeCalled();

        $this->execute($request);
    }

    public function it_throws_fraud_reclaim_exception_if_order_is_not_late_nor_paid_out(
        MarkOrderAsFraudRequest $request,
        OrderRepositoryInterface $orderRepository,
        OrderContainerFactory $orderContainerFactory,
        PaymentsServiceInterface $borscht,
        DebtorExternalDataEntity $debtorExternalData,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $orderContainerFactory
            ->loadByUuid(self::ORDER_UUID)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $order->isLate()->willReturn(false);
        $order->isPaidOut()->willReturn(false);

        $order->setMarkedAsFraudAt(Argument::type(\DateTime::class))->shouldBeCalled();
        $orderRepository->update($order)->shouldBeCalled();

        $debtorExternalData->isEstablishedCustomer()->willReturn(null);

        $borscht->createFraudReclaim(self::ORDER_PAYMENT_ID)->shouldNotBeCalled();

        $this->shouldThrow(FraudReclaimActionException::class)->during('execute', [$request]);
    }

    public function it_throws_fraud_reclaim_exception_if_order_delivery_address_is_same_as_debtor_address(
        MarkOrderAsFraudRequest $request,
        OrderRepositoryInterface $orderRepository,
        OrderContainerFactory $orderContainerFactory,
        PaymentsServiceInterface $borscht,
        DebtorExternalDataEntity $debtorExternalData,
        AddressEntity $debtorAddress,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $orderContainerFactory
            ->loadByUuid(self::ORDER_UUID)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $orderContainer->getDeliveryAddress()->willReturn($debtorAddress);

        $order->isLate()->willReturn(true);
        $order->isPaidOut()->willReturn(true);

        $order->setMarkedAsFraudAt(Argument::type(\DateTime::class))->shouldBeCalled();
        $orderRepository->update($order)->shouldBeCalled();

        $debtorExternalData->isEstablishedCustomer()->willReturn(null);

        $borscht->createFraudReclaim(self::ORDER_PAYMENT_ID)->shouldNotBeCalled();

        $this->shouldThrow(FraudReclaimActionException::class)->during('execute', [$request]);
    }

    public function it_throws_fraud_reclaim_exception_if_order_amount_is_less_than_the_limit_and_established_customer_is_not_null(
        MarkOrderAsFraudRequest $request,
        OrderRepositoryInterface $orderRepository,
        OrderContainerFactory $orderContainerFactory,
        PaymentsServiceInterface $borscht,
        DebtorExternalDataEntity $debtorExternalData,
        OrderEntity $order,
        OrderContainer $orderContainer,
        OrderFinancialDetailsEntity $orderFinancialDetails
    ) {
        $orderContainerFactory
            ->loadByUuid(self::ORDER_UUID)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $orderFinancialDetails->getAmountGross()->willReturn((new Money(2000))->subtract(1000));

        $order->isLate()->willReturn(true);
        $order->isPaidOut()->willReturn(true);

        $order->setMarkedAsFraudAt(Argument::type(\DateTime::class))->shouldBeCalled();
        $orderRepository->update($order)->shouldBeCalled();

        $debtorExternalData->isEstablishedCustomer()->willReturn(true);

        $borscht->createFraudReclaim(self::ORDER_PAYMENT_ID)->shouldNotBeCalled();

        $this->shouldThrow(FraudReclaimActionException::class)->during('execute', [$request]);
    }

    private function mockOrderData(
        OrderContainer $orderContainer,
        OrderEntity $order,
        AddressEntity $debtorAddress,
        AddressEntity $deliveryAddress,
        DebtorExternalDataEntity $debtorExternalData,
        OrderFinancialDetailsEntity $orderFinancialDetails
    ) {
        $order->getPaymentId()->willReturn(self::ORDER_PAYMENT_ID);
        $order->getMarkedAsFraudAt()->willReturn(null);

        $orderContainer->getOrder()->willReturn($order);
        $orderContainer->getDebtorExternalData()->willReturn($debtorExternalData);
        $orderContainer->getDebtorExternalDataAddress()->willReturn($debtorAddress);
        $orderContainer->getDeliveryAddress()->willReturn($deliveryAddress);

        $orderContainer->getOrderFinancialDetails()->willReturn($orderFinancialDetails);
        $this->mockDebtorAddress($debtorAddress);
        $this->mockDeliveryAddress($deliveryAddress);
    }

    private function mockDeliveryAddress(AddressEntity $deliveryAddress)
    {
        $deliveryAddress->getCity()->willReturn(self::DELIVERY_ADDRESS_CITY);
        $deliveryAddress->getPostalCode()->willReturn(self::DELIVERY_ADDRESS_POSTAL_CODE);
        $deliveryAddress->getStreet()->willReturn(self::DELIVERY_ADDRESS_STREET);
        $deliveryAddress->getHouseNumber()->willReturn(self::DELIVERY_ADDRESS_HOUSE_NUMBER);
    }

    private function mockDebtorAddress(AddressEntity $debtorAddress)
    {
        $debtorAddress->getCity()->willReturn(self::DEBTOR_ADDRESS_CITY);
        $debtorAddress->getPostalCode()->willReturn(self::DEBTOR_ADDRESS_POSTAL_CODE);
        $debtorAddress->getStreet()->willReturn(self::DEBTOR_ADDRESS_STREET);
        $debtorAddress->getHouseNumber()->willReturn(self::DEBTOR_ADDRESS_HOUSE_NUMBER);
    }
}
