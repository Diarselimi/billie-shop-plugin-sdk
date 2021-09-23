<?php

namespace App\Application\UseCase\CancelOrder;

use App\Application\CommandHandler;
use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\DomainModel\Invoice\InvoiceCancellationService;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\OrderUpdate\UpdateOrderLimitsService;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Registry;

class CancelOrderUseCase implements LoggingInterface, CommandHandler
{
    use LoggingTrait;

    private OrderContainerFactory $orderContainerFactory;

    private Registry $workflowRegistry;

    private InvoiceCancellationService $invoiceCancellationService;

    private UpdateOrderLimitsService $updateLimitsService;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        UpdateOrderLimitsService $updateLimitsService,
        Registry $workflowRegistry,
        InvoiceCancellationService $invoiceCancellationService
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->workflowRegistry = $workflowRegistry;
        $this->invoiceCancellationService = $invoiceCancellationService;
        $this->updateLimitsService = $updateLimitsService;
    }

    public function execute(CancelOrderRequest $request): void
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalIdOrUuid(
                $request->getMerchantId(),
                $request->getOrderId()
            );
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        $order = $orderContainer->getOrder();
        $workflow = $this->workflowRegistry->get($order);

        if ($order->isWorkflowV1()) {
            throw new WorkflowException('Order workflow is not supported by api v2');
        }

        if ($orderContainer->getInvoices()->hasCompletedInvoice()
            || $orderContainer->getInvoices()->hasPartiallyPaidInvoice()
        ) {
            throw new WorkflowException("Order can't be canceled anymore, there are paid back invoices");
        }

        if ($workflow->can($order, OrderEntity::TRANSITION_CANCEL_EXPLICITLY)) {
            if (!$orderContainer->getOrderFinancialDetails()->getUnshippedAmountGross()->isZero()) {
                $newLockedAmount = $orderContainer->getOrderFinancialDetails()->getAmountGross()->subtract(
                    $orderContainer->getOrderFinancialDetails()->getUnshippedAmountGross()
                );

                $this->updateLimitsService->updateLimitAmounts($orderContainer, $newLockedAmount);
            }

            $workflow->apply($order, OrderEntity::TRANSITION_CANCEL_EXPLICITLY);

            foreach ($orderContainer->getInvoices() as $invoice) {
                $this->invoiceCancellationService->cancelInvoiceFully($invoice);
            }
        } elseif ($workflow->can($order, OrderEntity::TRANSITION_CANCEL_WAITING)) {
            $workflow->apply($order, OrderEntity::TRANSITION_CANCEL_WAITING);
        } else {
            throw new CancelOrderException("Order #{$request->getOrderId()} can not be cancelled");
        }
    }
}
