<?php

namespace spec\App\DomainModel\OrderPayment;

use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Borscht\OrderAmountChangeDTO;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderPayment\OrderPaymentForgivenessService;
use PhpSpec\ObjectBehavior;
use Psr\Log\NullLogger;

class OrderPaymentForgivenessServiceSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(OrderPaymentForgivenessService::class);
    }

    public function let(
        BorschtInterface $paymentsService,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository
    ) {
        $this->beConstructedWith(...func_get_args());
        $this->setLogger(new NullLogger());
    }

    public function it_should_trigger_merchant_payment_if_debtor_partially_paid_and_outstanding_is_less_than_forgiveness_threshold(
        BorschtInterface $paymentsService,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository
    ) {
        $amountChange = (new OrderAmountChangeDTO())->setPaidAmount(75)->setOutstandingAmount(0.9);
        $order = (new OrderEntity())->setId(1)->setMerchantId(1);
        $merchantSettings = (new MerchantSettingsEntity())->setId(1)->setDebtorForgivenessThreshold(1.0);

        $merchantSettingsRepository->getOneByMerchantOrFail($order->getMerchantId())->shouldBeCalledOnce()->willReturn($merchantSettings);
        $paymentsService->confirmPayment($order, $amountChange->getOutstandingAmount())->shouldBeCalledOnce();

        $this->begForgiveness($order, $amountChange)->shouldBe(true);
    }

    public function it_should_trigger_merchant_payment_if_debtor_partially_paid_and_outstanding_equals_forgiveness_threshold(
        BorschtInterface $paymentsService,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository
    ) {
        $amountChange = (new OrderAmountChangeDTO())->setPaidAmount(75)->setOutstandingAmount(1.0);
        $order = (new OrderEntity())->setId(1)->setMerchantId(1);
        $merchantSettings = (new MerchantSettingsEntity())->setId(1)->setDebtorForgivenessThreshold(1.0);

        $merchantSettingsRepository->getOneByMerchantOrFail($order->getMerchantId())->shouldBeCalledOnce()->willReturn($merchantSettings);
        $paymentsService->confirmPayment($order, $amountChange->getOutstandingAmount())->shouldBeCalledOnce();

        $this->begForgiveness($order, $amountChange)->shouldBe(true);
    }

    public function it_should_not_trigger_merchant_payment_if_forgiveness_threshold_is_zero(
        BorschtInterface $paymentsService,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository
    ) {
        $amountChange = (new OrderAmountChangeDTO())->setPaidAmount(75)->setOutstandingAmount(1.0);
        $order = (new OrderEntity())->setId(1)->setMerchantId(1);
        $merchantSettings = (new MerchantSettingsEntity())->setId(1)->setDebtorForgivenessThreshold(0);

        $merchantSettingsRepository->getOneByMerchantOrFail($order->getMerchantId())->shouldBeCalledOnce()->willReturn($merchantSettings);
        $paymentsService->confirmPayment($order, $amountChange->getOutstandingAmount())->shouldNotBeCalled();

        $this->begForgiveness($order, $amountChange)->shouldBe(false);
    }

    public function it_should_not_trigger_merchant_payment_if_debtor_did_not_pay_anything_yet(
        BorschtInterface $paymentsService,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository
    ) {
        $amountChange = (new OrderAmountChangeDTO())->setPaidAmount(0)->setOutstandingAmount(0.5);
        $order = (new OrderEntity())->setId(1)->setMerchantId(1);
        $merchantSettings = (new MerchantSettingsEntity())->setId(1)->setDebtorForgivenessThreshold(1.0);

        $merchantSettingsRepository->getOneByMerchantOrFail($order->getMerchantId())->shouldBeCalledOnce()->willReturn($merchantSettings);
        $paymentsService->confirmPayment($order, $amountChange->getOutstandingAmount())->shouldNotBeCalled();

        $this->begForgiveness($order, $amountChange)->shouldBe(false);
    }

    public function it_should_not_trigger_merchant_payment_if_debtor_partially_paid_and_outstanding_is_over_forgiveness_threshold(
        BorschtInterface $paymentsService,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository
    ) {
        $amountChange = (new OrderAmountChangeDTO())->setPaidAmount(50)->setOutstandingAmount(2);
        $order = (new OrderEntity())->setId(1)->setMerchantId(1);
        $merchantSettings = (new MerchantSettingsEntity())->setId(1)->setDebtorForgivenessThreshold(1.0);

        $merchantSettingsRepository->getOneByMerchantOrFail($order->getMerchantId())->shouldBeCalledOnce()->willReturn($merchantSettings);
        $paymentsService->confirmPayment($order, $amountChange->getOutstandingAmount())->shouldNotBeCalled();

        $this->begForgiveness($order, $amountChange)->shouldBe(false);
    }

    public function it_should_not_trigger_merchant_payment_if_outstanding_amount_is_zero(
        BorschtInterface $paymentsService,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository
    ) {
        $amountChange = (new OrderAmountChangeDTO())->setPaidAmount(50)->setOutstandingAmount(0);
        $order = (new OrderEntity())->setId(1)->setMerchantId(1);
        $merchantSettings = (new MerchantSettingsEntity())->setId(1)->setDebtorForgivenessThreshold(1.0);

        $merchantSettingsRepository->getOneByMerchantOrFail($order->getMerchantId())->shouldBeCalledOnce()->willReturn($merchantSettings);
        $paymentsService->confirmPayment($order, $amountChange->getOutstandingAmount())->shouldNotBeCalled();

        $this->begForgiveness($order, $amountChange)->shouldBe(false);
    }
}
