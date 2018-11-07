<?php

namespace spec\App\Application\UseCase\MarkOrderAsFraud;

use App\Application\Exception\FraudOrderException;
use App\Application\UseCase\MarkOrderAsFraud\MarkOrderAsFraudRequest;
use App\Application\UseCase\MarkOrderAsFraud\MarkOrderAsFraudUseCase;
use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MarkOrderAsFraudUseCaseSpec extends ObjectBehavior
{
    const ORDER_UUID = 'test';

    const ORDER_PAYMENT_ID = 'DIDI';

    const DELIVERY_ADDRESS_ID = 1;

    const DELIVERY_ADDRESS_CITY = 'Berlin';

    const DELIVERY_ADDRESS_POSTAL_CODE = '10999';

    const DELIVERY_ADDRESS_STREET = 'testStr';

    const DELIVERY_ADDRESS_HOUSE_NUMBER = '11';

    const DEBTOR_EXTERNAL_DATA_ID = 1;

    const DEBTOR_ADDRESS_ID = 2;

    const DEBTOR_ADDRESS_CITY = 'Hanover';

    const DEBTOR_ADDRESS_POSTAL_CODE = '10999';

    const DEBTOR_ADDRESS_STREET = 'testStr';

    const DEBTOR_ADDRESS_HOUSE_NUMBER = '11';

    public function let(
        MarkOrderAsFraudRequest $request,
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        AddressRepositoryInterface $addressRepository,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        BorschtInterface $borscht
    ) {
        $request->getUuid()->willReturn(self::ORDER_UUID);

        $this->beConstructedWith(
            $orderRepository,
            $orderStateManager,
            $addressRepository,
            $debtorExternalDataRepository,
            $borscht
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MarkOrderAsFraudUseCase::class);
    }

    public function it_throws_exception_if_order_was_not_found(
        OrderRepositoryInterface $orderRepository,
        MarkOrderAsFraudRequest $request
    ) {
        $orderRepository->getOneByUuid(self::ORDER_UUID)->shouldBeCalled()->willReturn(null);

        $this->shouldThrow(OrderNotFoundException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_order_was_already_marked_as_fraud(
        OrderRepositoryInterface $orderRepository,
        MarkOrderAsFraudRequest $request,
        OrderEntity $orderEntity
    ) {
        $orderEntity->getMarkedAsFraudAt()->willReturn(new \DateTime());

        $orderRepository->getOneByUuid(self::ORDER_UUID)->shouldBeCalled()->willReturn($orderEntity);

        $this->shouldThrow(FraudOrderException::class)->during('execute', [$request]);
    }

    public function it_sets_marked_as_fraud_at_date_to_current_date_time_and_call_borscht_to_create_fraud_reclaim(
        MarkOrderAsFraudRequest $request,
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        AddressRepositoryInterface $addressRepository,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        BorschtInterface $borscht,
        OrderEntity $order,
        AddressEntity $deliveryAddress,
        AddressEntity $debtorAddress,
        DebtorExternalDataEntity $debtorExternalData
    ) {
        $orderRepository->getOneByUuid(self::ORDER_UUID)->shouldBeCalled()->willReturn($order);
        $order->getPaymentId()->willReturn(self::ORDER_PAYMENT_ID);
        $order->getMarkedAsFraudAt()->willReturn(null);
        $order->getDeliveryAddressId()->willReturn(self::DELIVERY_ADDRESS_ID);
        $order->getDebtorExternalDataId()->willReturn(self::DEBTOR_EXTERNAL_DATA_ID);
        $order->getAmountGross()->willReturn(MarkOrderAsFraudUseCase::ORDER_AMOUNT_LIMIT + 10);

        $orderStateManager->isLate($order)->willReturn(true);
        $orderStateManager->isPaidOut($order)->willReturn(true);

        $order->setMarkedAsFraudAt(Argument::type(\DateTime::class))->shouldBeCalled();
        $orderRepository->update($order)->shouldBeCalled();

        $addressRepository->getOneById(self::DELIVERY_ADDRESS_ID)->shouldBeCalled()->willReturn($deliveryAddress);
        $deliveryAddress->getCity()->willReturn(self::DELIVERY_ADDRESS_CITY);
        $deliveryAddress->getPostalCode()->willReturn(self::DELIVERY_ADDRESS_POSTAL_CODE);
        $deliveryAddress->getStreet()->willReturn(self::DELIVERY_ADDRESS_STREET);
        $deliveryAddress->getHouseNumber()->willReturn(self::DELIVERY_ADDRESS_HOUSE_NUMBER);

        $debtorExternalDataRepository->getOneById(self::DEBTOR_EXTERNAL_DATA_ID)->shouldBeCalled()->willReturn($debtorExternalData);
        $debtorExternalData->getAddressId()->willReturn(self::DEBTOR_ADDRESS_ID);
        $debtorExternalData->isEstablishedCustomer()->willReturn(null);

        $addressRepository->getOneById(self::DEBTOR_ADDRESS_ID)->shouldBeCalled()->willReturn($debtorAddress);
        $debtorAddress->getCity()->willReturn(self::DEBTOR_ADDRESS_CITY);
        $debtorAddress->getPostalCode()->willReturn(self::DEBTOR_ADDRESS_POSTAL_CODE);
        $debtorAddress->getStreet()->willReturn(self::DEBTOR_ADDRESS_STREET);
        $debtorAddress->getHouseNumber()->willReturn(self::DEBTOR_ADDRESS_HOUSE_NUMBER);

        $borscht->createFraudReclaim(self::ORDER_PAYMENT_ID)->shouldBeCalled();

        $this->execute($request);
    }
}
