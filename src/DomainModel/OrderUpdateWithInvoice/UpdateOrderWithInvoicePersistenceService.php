<?php

namespace App\DomainModel\OrderUpdateWithInvoice;

use App\Application\UseCase\UpdateOrderWithInvoice\UpdateOrderWithInvoiceRequest;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsPersistenceService;
use App\DomainModel\OrderUpdate\UpdateOrderLimitsService;
use App\DomainModel\Payment\PaymentRequestFactory;
use App\DomainModel\Payment\PaymentsServiceInterface;

class UpdateOrderWithInvoicePersistenceService
{
    private $paymentsService;

    private $orderStateManager;

    private $paymentRequestFactory;

    private $updateOrderWithInvoiceRequestValidator;

    private $financialDetailsPersistenceService;

    private $updateOrderLimitsService;

    public function __construct(
        PaymentsServiceInterface $paymentsService,
        OrderStateManager $orderStateManager,
        PaymentRequestFactory $paymentRequestFactory,
        UpdateOrderWithInvoiceRequestValidator $updateOrderWithInvoiceRequestValidator,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        UpdateOrderLimitsService $updateOrderLimitsService
    ) {
        $this->paymentsService = $paymentsService;
        $this->orderStateManager = $orderStateManager;
        $this->paymentRequestFactory = $paymentRequestFactory;
        $this->updateOrderWithInvoiceRequestValidator = $updateOrderWithInvoiceRequestValidator;
        $this->financialDetailsPersistenceService = $financialDetailsPersistenceService;
        $this->updateOrderLimitsService = $updateOrderLimitsService;
    }

    public function update(
        OrderContainer $orderContainer,
        UpdateOrderWithInvoiceRequest $request
    ): UpdateOrderWithInvoiceRequest {
        $order = $orderContainer->getOrder();
        $changeSet = $this->updateOrderWithInvoiceRequestValidator->getValidatedRequest(
            $orderContainer,
            $request
        );

        $amountChanged = $changeSet->getAmount() !== null;

        if ($amountChanged && !$this->orderStateManager->wasShipped($order)) {
            $this->updateOrderLimitsService->unlockLimits($orderContainer, $changeSet);
        }

        if ($amountChanged) {
            $this->financialDetailsPersistenceService->updateFinancialDetails(
                $orderContainer,
                $changeSet,
                $orderContainer->getOrderFinancialDetails()->getDuration()
            );
        }

        if ($amountChanged && $this->orderStateManager->wasShipped($order)) {
            $this->paymentsService->modifyOrder(
                $this->paymentRequestFactory->createModifyRequestDTO($orderContainer)
            );
        }

        return $changeSet;
    }
}
