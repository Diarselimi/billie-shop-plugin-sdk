<?php

namespace spec\App\DomainModel\OrderUpdate;

use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsFactory;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsPersistenceService;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use App\DomainModel\OrderInvoice\InvoiceUploadHandlerInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use App\DomainModel\OrderInvoice\OrderInvoiceUploadException;
use App\DomainModel\OrderUpdate\UpdateOrderException;
use App\DomainModel\OrderUpdate\UpdateOrderLimitsService;
use App\DomainModel\OrderUpdate\UpdateOrderPersistenceService;
use App\DomainModel\OrderUpdate\UpdateOrderRequestValidator;
use App\DomainModel\Payment\PaymentRequestFactory;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Payment\RequestDTO\ModifyRequestDTO;
use Ozean12\Money\Money;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UpdateOrderPersistenceServiceSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(UpdateOrderPersistenceService::class);
    }

    public function let(
        PaymentsServiceInterface $paymentsService,
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderInvoiceManager $invoiceManager,
        PaymentRequestFactory $paymentRequestFactory,
        UpdateOrderLimitsService $updateOrderLimitsService,
        UpdateOrderRequestValidator $updateOrderRequestValidator
    ) {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_updates_amount_but_not_limits_if_order_was_shipped(
        OrderContainer $orderContainer,
        UpdateOrderRequest $request,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        UpdateOrderLimitsService $updateOrderLimitsService,
        MerchantEntity $merchant,
        MerchantRepositoryInterface $merchantRepository,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderRepositoryInterface $orderRepository,
        OrderInvoiceManager $invoiceManager,
        OrderStateManager $orderStateManager,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentsServiceInterface $paymentsService
    ) {
        $changeSet = (new UpdateOrderRequest('order123', 1))->setAmount(
            TaxedMoneyFactory::create(150, 150, 0)
        );
        $orderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(200))
            ->setAmountNet(new Money(200))
            ->setAmountTax(new Money(0))
            ->setDuration(30)
            ->setOrderId(1);
        $newOrderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(150))
            ->setAmountNet(new Money(150))
            ->setAmountTax(new Money(0))
            ->setDuration(30)
            ->setOrderId(1);

        $grossDiff = new Money(50);
        $order = new OrderEntity();

        $orderContainer->getOrder()->shouldBeCalled()->willReturn($order);
        $updateOrderRequestValidator->getValidatedRequest($orderContainer, $request)->shouldBeCalled()->willReturn($changeSet);
        $orderContainer->getOrderFinancialDetails()->shouldBeCalled()->willReturn($orderFinancialDetails);
        $orderContainer->getMerchant()->shouldNotBeCalled();

        // should NOT unlock merchant debtor limit
        $updateOrderLimitsService->unlockLimits($orderContainer, $grossDiff)->shouldNotBeCalled();

        // should NOT unlock merchant limit
        $merchant->increaseFinancingLimit($grossDiff)->shouldNotBeCalled();
        $merchantRepository->update($merchant)->shouldNotBeCalled();

        // update financial details
        $financialDetailsPersistenceService->updateFinancialDetails(
            $orderContainer,
            $changeSet,
            $newOrderFinancialDetails->getDuration()
        )->shouldBeCalled();

        // should NOT update order nor invoice
        $orderRepository->update(Argument::any())->shouldNotBeCalled();
        $invoiceManager->upload(Argument::any(), Argument::any())->shouldNotBeCalled();

        // calls payments service
        $orderStateManager->wasShipped($order)->shouldBeCalled()->willReturn(true);
        $paymentsModifyRequest = new ModifyRequestDTO();
        $paymentRequestFactory->createModifyRequestDTO($orderContainer)->shouldBeCalled()->willReturn($paymentsModifyRequest);
        $paymentsService->modifyOrder($paymentsModifyRequest)->shouldBeCalled();

        // run
        $this->update($orderContainer, $request)->shouldReturn($changeSet);
    }

    public function it_updates_amount_and_limits_if_order_was_not_shipped(
        OrderContainer $orderContainer,
        UpdateOrderRequest $request,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        UpdateOrderLimitsService $updateOrderLimitsService,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderRepositoryInterface $orderRepository,
        OrderInvoiceManager $invoiceManager,
        OrderStateManager $orderStateManager,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentsServiceInterface $paymentsService
    ) {
        $changeSet = (new UpdateOrderRequest('order123', 1))->setAmount(
            TaxedMoneyFactory::create(150, 150, 0)
        );
        $orderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(200))
            ->setAmountNet(new Money(200))
            ->setAmountTax(new Money(0))
            ->setDuration(30)
            ->setOrderId(1);
        $newOrderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(150))
            ->setAmountNet(new Money(150))
            ->setAmountTax(new Money(0))
            ->setDuration(30)
            ->setOrderId(1);

        $order = new OrderEntity();

        $orderContainer->getOrder()->shouldBeCalled()->willReturn($order);
        $updateOrderRequestValidator->getValidatedRequest($orderContainer, $request)->shouldBeCalled()->willReturn($changeSet);
        $orderContainer->getOrderFinancialDetails()->shouldBeCalled()->willReturn($orderFinancialDetails);

        // unlocks merchant debtor limit
        $updateOrderLimitsService->unlockLimits($orderContainer, $changeSet)->shouldBeCalled();

        // update financial details
        $financialDetailsPersistenceService->updateFinancialDetails(
            $orderContainer,
            $changeSet,
            $newOrderFinancialDetails->getDuration()
        )->shouldBeCalled();

        // should NOT update order nor invoice
        $orderRepository->update(Argument::any())->shouldNotBeCalled();
        $invoiceManager->upload(Argument::any(), Argument::any())->shouldNotBeCalled();

        // should NOT call payments service
        $orderStateManager->wasShipped($order)->shouldBeCalled()->willReturn(false);
        $paymentRequestFactory->createModifyRequestDTO(Argument::any())->shouldNotBeCalled();
        $paymentsService->modifyOrder(Argument::any())->shouldNotBeCalled();

        // run
        $this->update($orderContainer, $request)->shouldReturn($changeSet);
    }

    public function it_updates_duration(
        OrderContainer $orderContainer,
        UpdateOrderRequest $request,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        UpdateOrderLimitsService $updateOrderLimitsService,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderRepositoryInterface $orderRepository,
        OrderInvoiceManager $invoiceManager,
        OrderStateManager $orderStateManager,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentsServiceInterface $paymentsService
    ) {
        $changeSet = (new UpdateOrderRequest('order123', 1))->setDuration(60);
        $newOrderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(200))
            ->setAmountNet(new Money(200))
            ->setAmountTax(new Money(0))
            ->setDuration(60)
            ->setOrderId(1);

        $order = new OrderEntity();

        $orderContainer->getOrder()->shouldBeCalled()->willReturn($order);
        $updateOrderRequestValidator->getValidatedRequest($orderContainer, $request)->shouldBeCalled()->willReturn($changeSet);

        // it does NOT unlock limits
        $updateOrderLimitsService->unlockLimits($orderContainer, Argument::any())->shouldNotBeCalled();

        // update financial details
        $financialDetailsPersistenceService->updateFinancialDetails(
            $orderContainer,
            $changeSet,
            $newOrderFinancialDetails->getDuration()
        )->shouldBeCalled();

        // should NOT update order nor invoice
        $orderRepository->update(Argument::any())->shouldNotBeCalled();
        $invoiceManager->upload(Argument::any(), Argument::any())->shouldNotBeCalled();

        // calls payments service
        $orderStateManager->wasShipped($order)->shouldBeCalled()->willReturn(true);
        $paymentsModifyRequest = new ModifyRequestDTO();
        $paymentRequestFactory->createModifyRequestDTO($orderContainer)->shouldBeCalled()->willReturn($paymentsModifyRequest);
        $paymentsService->modifyOrder($paymentsModifyRequest)->shouldBeCalled();

        // run
        $this->update($orderContainer, $request)->shouldReturn($changeSet);
    }

    public function it_updates_invoice(
        OrderContainer $orderContainer,
        UpdateOrderRequest $request,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        UpdateOrderLimitsService $updateOrderLimitsService,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderRepositoryInterface $orderRepository,
        OrderInvoiceManager $invoiceManager,
        OrderStateManager $orderStateManager,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentsServiceInterface $paymentsService
    ) {
        $changeSet = (new UpdateOrderRequest('order123', 1))->setInvoiceNumber('foobar')->setInvoiceUrl('foobar.pdf');
        $order = new OrderEntity();

        $orderContainer->getOrder()->shouldBeCalled()->willReturn($order);
        $updateOrderRequestValidator->getValidatedRequest($orderContainer, $request)->shouldBeCalled()->willReturn($changeSet);

        // it does NOT unlock limits
        $updateOrderLimitsService->unlockLimits($orderContainer, Argument::any())->shouldNotBeCalled();

        // it does NOT update financial details
        $financialDetailsPersistenceService->updateFinancialDetails(
            Argument::cetera()
        )->shouldNotBeCalled();

        // update order and invoice
        $orderRepository->update($order)->shouldBeCalled();
        $invoiceManager->upload($order, InvoiceUploadHandlerInterface::EVENT_UPDATE)->shouldBeCalled();

        // calls payments service
        $orderStateManager->wasShipped($order)->shouldBeCalled()->willReturn(true);
        $paymentsModifyRequest = new ModifyRequestDTO();
        $paymentRequestFactory->createModifyRequestDTO($orderContainer)->shouldBeCalled()->willReturn($paymentsModifyRequest);
        $paymentsService->modifyOrder($paymentsModifyRequest)->shouldBeCalled();

        // run
        $this->update($orderContainer, $request)->shouldReturn($changeSet);
    }

    public function it_updates_external_code(
        OrderContainer $orderContainer,
        UpdateOrderRequest $request,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        UpdateOrderLimitsService $updateOrderLimitsService,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderRepositoryInterface $orderRepository,
        OrderInvoiceManager $invoiceManager,
        OrderStateManager $orderStateManager,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentsServiceInterface $paymentsService
    ) {
        $changeSet = (new UpdateOrderRequest('order123', 1))->setExternalCode('foobar001');
        $order = new OrderEntity();

        $orderContainer->getOrder()->shouldBeCalled()->willReturn($order);
        $updateOrderRequestValidator->getValidatedRequest($orderContainer, $request)->shouldBeCalled()->willReturn($changeSet);

        // it does NOT unlock limits
        $updateOrderLimitsService->unlockLimits($orderContainer, Argument::any())->shouldNotBeCalled();

        // it does NOT update financial details
        $financialDetailsPersistenceService->updateFinancialDetails(
            Argument::cetera()
        )->shouldNotBeCalled();

        // update order
        $orderRepository->update($order)->shouldBeCalled();

        // it does NOT update invoice
        $invoiceManager->upload(Argument::any(), Argument::any())->shouldNotBeCalled();

        // it does not call payments service
        $orderStateManager->wasShipped(Argument::any())->shouldNotBeCalled();
        $paymentRequestFactory->createModifyRequestDTO(Argument::any())->shouldNotBeCalled();
        $paymentsService->modifyOrder(Argument::any())->shouldNotBeCalled();

        // run
        $this->update($orderContainer, $request)->shouldReturn($changeSet);
    }

    public function it_does_not_update_anything(
        OrderContainer $orderContainer,
        UpdateOrderRequest $request,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        UpdateOrderLimitsService $updateOrderLimitsService,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderRepositoryInterface $orderRepository,
        OrderInvoiceManager $invoiceManager,
        OrderStateManager $orderStateManager,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentsServiceInterface $paymentsService
    ) {
        $changeSet = (new UpdateOrderRequest('order123', 1));
        $orderContainer->getOrder()->shouldBeCalled()->willReturn(new OrderEntity());
        $updateOrderRequestValidator->getValidatedRequest($orderContainer, $request)->shouldBeCalled()->willReturn($changeSet);

        // it does NOT unlock limits
        $updateOrderLimitsService->unlockLimits($orderContainer, Argument::any())->shouldNotBeCalled();

        // it does NOT update financial details
        $financialDetailsPersistenceService->updateFinancialDetails(
            Argument::cetera()
        )->shouldNotBeCalled();

        // it does NOT update order
        $orderRepository->update(Argument::any())->shouldNotBeCalled();

        // it does NOT update invoice
        $invoiceManager->upload(Argument::any(), Argument::any())->shouldNotBeCalled();

        // it does not call payments service
        $orderStateManager->wasShipped(Argument::any())->shouldNotBeCalled();
        $paymentRequestFactory->createModifyRequestDTO(Argument::any())->shouldNotBeCalled();
        $paymentsService->modifyOrder(Argument::any())->shouldNotBeCalled();

        // run
        $this->update($orderContainer, $request)->shouldReturn($changeSet);
    }

    public function it_does_not_call_payments_if_order_was_not_shipped(
        OrderContainer $orderContainer,
        UpdateOrderRequest $request,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        UpdateOrderLimitsService $updateOrderLimitsService,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderRepositoryInterface $orderRepository,
        OrderInvoiceManager $invoiceManager,
        OrderStateManager $orderStateManager,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentsServiceInterface $paymentsService
    ) {
        $changeSet = (new UpdateOrderRequest('order123', 1))->setAmount(
            TaxedMoneyFactory::create(150, 150, 0)
        );
        $orderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(200))
            ->setAmountNet(new Money(200))
            ->setAmountTax(new Money(0))
            ->setDuration(30)
            ->setOrderId(1);
        $newOrderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(150))
            ->setAmountNet(new Money(150))
            ->setAmountTax(new Money(0))
            ->setDuration(30)
            ->setOrderId(1);

        $grossDiff = new Money(50);
        $order = new OrderEntity();

        $orderContainer->getOrder()->shouldBeCalled()->willReturn($order);
        $updateOrderRequestValidator->getValidatedRequest($orderContainer, $request)->shouldBeCalled()->willReturn($changeSet);
        $orderContainer->getOrderFinancialDetails()->shouldBeCalled()->willReturn($orderFinancialDetails);

        // unlocks merchant debtor limit
        $updateOrderLimitsService->unlockLimits($orderContainer, $changeSet)->shouldBeCalled();

        // update financial details
        $financialDetailsPersistenceService->updateFinancialDetails(
            $orderContainer,
            $changeSet,
            $newOrderFinancialDetails->getDuration()
        )->shouldBeCalled();

        // should NOT update order nor invoice
        $orderRepository->update(Argument::any())->shouldNotBeCalled();
        $invoiceManager->upload(Argument::any(), Argument::any())->shouldNotBeCalled();

        // it does NOT call payments service
        $orderStateManager->wasShipped($order)->shouldBeCalled()->willReturn(false);
        $paymentRequestFactory->createModifyRequestDTO(Argument::any())->shouldNotBeCalled();
        $paymentsService->modifyOrder(Argument::any())->shouldNotBeCalled();

        // run
        $this->update($orderContainer, $request)->shouldReturn($changeSet);
    }

    public function it_fails_on_upload_invoice(
        OrderContainer $orderContainer,
        UpdateOrderRequest $request,
        UpdateOrderRequestValidator $updateOrderRequestValidator,
        UpdateOrderLimitsService $updateOrderLimitsService,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderRepositoryInterface $orderRepository,
        OrderInvoiceManager $invoiceManager,
        OrderStateManager $orderStateManager,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentsServiceInterface $paymentsService
    ) {
        $changeSet = (new UpdateOrderRequest('order123', 1))->setInvoiceNumber('foobar')->setInvoiceUrl('foobar.pdf');
        $order = new OrderEntity();

        $orderContainer->getOrder()->shouldBeCalled()->willReturn($order);
        $updateOrderRequestValidator->getValidatedRequest($orderContainer, $request)->shouldBeCalled()->willReturn($changeSet);

        // it does NOT unlock limits
        $updateOrderLimitsService->unlockLimits($orderContainer, Argument::any())->shouldNotBeCalled();

        // it does NOT update financial details
        $financialDetailsPersistenceService->updateFinancialDetails(
            Argument::cetera()
        )->shouldNotBeCalled();

        // update order and invoice
        $orderRepository->update($order)->shouldBeCalled();
        $invoiceManager->upload($order, InvoiceUploadHandlerInterface::EVENT_UPDATE)
            ->shouldBeCalled()->willThrow(OrderInvoiceUploadException::class);

        // it does NOT call payments service
        $orderStateManager->wasShipped($order)->shouldNotBeCalled();
        $paymentRequestFactory->createModifyRequestDTO(Argument::any())->shouldNotBeCalled();
        $paymentsService->modifyOrder(Argument::any())->shouldNotBeCalled();

        // run
        $this->shouldThrow(UpdateOrderException::class)->during('update', [$orderContainer, $request]);
    }
}
