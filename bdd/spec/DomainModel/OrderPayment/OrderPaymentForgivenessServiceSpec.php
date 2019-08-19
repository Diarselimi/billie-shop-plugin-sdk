<?php

namespace spec\App\DomainModel\OrderPayment;

use App\DomainModel\Payment\PaymentRequestFactory;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Payment\OrderAmountChangeDTO;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderPayment\OrderPaymentForgivenessService;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\NullLogger;

class OrderPaymentForgivenessServiceSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(OrderPaymentForgivenessService::class);
    }

    public function let(
        PaymentsServiceInterface $paymentsService,
        OrderRepositoryInterface $orderRepository,
        PaymentRequestFactory $paymentRequestFactory,
        OrderContainer $orderContainer,
        OrderEntity $order,
        MerchantSettingsEntity $merchantSettings
    ) {
        $orderContainer->getOrder()->willReturn($order);
        $orderContainer->getMerchantSettings()->willReturn($merchantSettings);
        $order->getId()->willReturn(100);

        $merchantSettings->getDebtorForgivenessThreshold()->willReturn(1.0);

        $this->beConstructedWith(...func_get_args());
        $this->setLogger(new NullLogger());
    }

    public function it_should_trigger_merchant_payment_if_debtor_partially_paid_and_outstanding_is_less_than_forgiveness_threshold(
        PaymentsServiceInterface $paymentsService,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderEntity $order,
        PaymentRequestFactory $paymentRequestFactory
    ) {
        $paymentRequestFactory
            ->createConfirmRequestDTO(
                Argument::any(),
                Argument::any()
            )->shouldBeCalled();
        $amountChange->getPaidAmount()->willReturn(75);
        $amountChange->getOutstandingAmount()->willReturn(0.9);

        $order->getAmountForgiven()->willReturn(0);
        $order->setAmountForgiven(0.9)->shouldBeCalled();

        $paymentsService->confirmPayment(Argument::any())->shouldBeCalledOnce();

        $this->begForgiveness($orderContainer, $amountChange)->shouldBe(true);
    }

    public function it_should_store_amount_forgiven_if_successful(
        PaymentsServiceInterface $paymentsService,
        OrderRepositoryInterface $orderRepository,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderEntity $order,
        PaymentRequestFactory $paymentRequestFactory
    ) {
        $amountChange->getPaidAmount()->willReturn(75);
        $amountChange->getOutstandingAmount()->willReturn(0.9);
        $paymentRequestFactory->createConfirmRequestDTO(Argument::any(), Argument::any())->shouldBeCalled();

        $order->getAmountForgiven()->willReturn(0);
        $order->setAmountForgiven(0.9)->shouldBeCalled();

        $paymentsService->confirmPayment(Argument::any())->shouldBeCalledOnce();

        $orderRepository->update($order)->shouldBeCalledOnce();

        $this->begForgiveness($orderContainer, $amountChange)->shouldBe(true);
    }

    public function it_should_not_call_payments_if_amount_forgiven_is_greater_than_zero(
        PaymentsServiceInterface $paymentsService,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderEntity $order,
        PaymentRequestFactory $paymentRequestFactory
    ) {
        $amountChange->getPaidAmount()->willReturn(75);
        $amountChange->getOutstandingAmount()->willReturn(0.9);

        $order->getAmountForgiven()->willReturn(0.3);
        $order->setAmountForgiven(Argument::any())->shouldNotBeCalled();

        $paymentsService->confirmPayment(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->begForgiveness($orderContainer, $amountChange)->shouldBe(false);
    }

    public function it_should_trigger_merchant_payment_if_debtor_partially_paid_and_outstanding_equals_forgiveness_threshold(
        PaymentsServiceInterface $paymentsService,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderEntity $order,
        PaymentRequestFactory $paymentRequestFactory
    ) {
        $paymentRequestFactory->createConfirmRequestDTO(Argument::any(), Argument::any())->shouldBeCalled();

        $amountChange->getPaidAmount()->willReturn(75);
        $amountChange->getOutstandingAmount()->willReturn(1.0);

        $order->getAmountForgiven()->willReturn(0);
        $order->setAmountForgiven(1.0)->shouldBeCalled();

        $paymentsService->confirmPayment(Argument::any())->shouldBeCalledOnce();

        $this->begForgiveness($orderContainer, $amountChange)->shouldBe(true);
    }

    public function it_should_not_trigger_merchant_payment_if_forgiveness_threshold_is_zero(
        PaymentsServiceInterface $paymentsService,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderEntity $order,
        MerchantSettingsEntity $merchantSettings
    ) {
        $amountChange->getPaidAmount()->willReturn(75);
        $amountChange->getOutstandingAmount()->willReturn(1.0);

        $order->getAmountForgiven()->willReturn(0);
        $order->setAmountForgiven(Argument::any())->shouldNotBeCalled();
        $merchantSettings->getDebtorForgivenessThreshold()->willReturn(0);

        $paymentsService->confirmPayment(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->begForgiveness($orderContainer, $amountChange)->shouldBe(false);
    }

    public function it_should_not_trigger_merchant_payment_if_debtor_did_not_pay_anything_yet(
        PaymentsServiceInterface $paymentsService,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderEntity $order
    ) {
        $amountChange->getPaidAmount()->willReturn(0);
        $amountChange->getOutstandingAmount()->willReturn(0.5);

        $order->getAmountForgiven()->willReturn(0);
        $order->setAmountForgiven(Argument::any())->shouldNotBeCalled();

        $paymentsService->confirmPayment(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->begForgiveness($orderContainer, $amountChange)->shouldBe(false);
    }

    public function it_should_not_trigger_merchant_payment_if_debtor_partially_paid_and_outstanding_is_over_forgiveness_threshold(
        PaymentsServiceInterface $paymentsService,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderEntity $order
    ) {
        $amountChange->getPaidAmount()->willReturn(50);
        $amountChange->getOutstandingAmount()->willReturn(2);

        $order->getAmountForgiven()->willReturn(0);
        $order->setAmountForgiven(Argument::any())->shouldNotBeCalled();

        $paymentsService->confirmPayment(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->begForgiveness($orderContainer, $amountChange)->shouldBe(false);
    }

    public function it_should_not_trigger_merchant_payment_if_outstanding_amount_is_zero(
        PaymentsServiceInterface $paymentsService,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderEntity $order
    ) {
        $amountChange->getPaidAmount()->willReturn(50);
        $amountChange->getOutstandingAmount()->willReturn(0);

        $order->getAmountForgiven()->willReturn(0);
        $order->setAmountForgiven(Argument::any())->shouldNotBeCalled();

        $paymentsService->confirmPayment(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->begForgiveness($orderContainer, $amountChange)->shouldBe(false);
    }
}
