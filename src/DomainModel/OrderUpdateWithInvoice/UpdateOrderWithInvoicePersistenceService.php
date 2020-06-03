<?php

declare(strict_types=1);

namespace App\DomainModel\OrderUpdateWithInvoice;

use App\Application\UseCase\UpdateOrderWithInvoice\UpdateOrderWithInvoiceRequest;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderRepositoryInterface;
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

    private $orderRepository;

    public function __construct(
        PaymentsServiceInterface $paymentsService,
        OrderStateManager $orderStateManager,
        PaymentRequestFactory $paymentRequestFactory,
        UpdateOrderWithInvoiceRequestValidator $updateOrderWithInvoiceRequestValidator,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        UpdateOrderLimitsService $updateOrderLimitsService,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->paymentsService = $paymentsService;
        $this->orderStateManager = $orderStateManager;
        $this->paymentRequestFactory = $paymentRequestFactory;
        $this->updateOrderWithInvoiceRequestValidator = $updateOrderWithInvoiceRequestValidator;
        $this->financialDetailsPersistenceService = $financialDetailsPersistenceService;
        $this->updateOrderLimitsService = $updateOrderLimitsService;
        $this->orderRepository = $orderRepository;
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
        $invoiceNumberChanged = $changeSet->getInvoiceNumber() !== null;

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

        if ($invoiceNumberChanged) {
            $order->setInvoiceNumber($changeSet->getInvoiceNumber());
            $this->orderRepository->update($order);
        }

        if (($amountChanged || $invoiceNumberChanged) && $this->orderStateManager->wasShipped($order)) {
            $this->paymentsService->modifyOrder(
                $this->paymentRequestFactory->createModifyRequestDTO($orderContainer)
            );
        }

        return $changeSet;
    }
}
