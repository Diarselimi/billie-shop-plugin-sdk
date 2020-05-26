<?php

namespace App\DomainModel\OrderUpdate;

use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsFactory;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsPersistenceService;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use App\DomainModel\OrderInvoice\InvoiceUploadHandlerInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use App\DomainModel\OrderInvoice\OrderInvoiceUploadException;
use App\DomainModel\Payment\PaymentRequestFactory;
use App\DomainModel\Payment\PaymentsServiceInterface;

class UpdateOrderPersistenceService
{
    private $paymentsService;

    private $orderRepository;

    private $orderStateManager;

    private $invoiceManager;

    private $financialDetailsPersistenceService;

    private $updateOrderLimitsService;

    private $paymentRequestFactory;

    private $updateOrderRequestValidator;

    public function __construct(
        PaymentsServiceInterface $paymentsService,
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        OrderFinancialDetailsPersistenceService $financialDetailsPersistenceService,
        OrderInvoiceManager $invoiceManager,
        PaymentRequestFactory $paymentRequestFactory,
        UpdateOrderLimitsService $updateOrderLimitsService,
        UpdateOrderRequestValidator $updateOrderRequestValidator
    ) {
        $this->paymentsService = $paymentsService;
        $this->orderRepository = $orderRepository;
        $this->orderStateManager = $orderStateManager;
        $this->financialDetailsPersistenceService = $financialDetailsPersistenceService;
        $this->invoiceManager = $invoiceManager;
        $this->paymentRequestFactory = $paymentRequestFactory;
        $this->updateOrderLimitsService = $updateOrderLimitsService;
        $this->updateOrderRequestValidator = $updateOrderRequestValidator;
    }

    public function update(OrderContainer $orderContainer, UpdateOrderRequest $request): UpdateOrderRequest
    {
        $order = $orderContainer->getOrder();
        $changeSet = $this->updateOrderRequestValidator->getValidatedRequest($orderContainer, $request);

        $amountChanged = $changeSet->getAmount() !== null;
        $durationChanged = $changeSet->getDuration() !== null;
        $invoiceChanged = $changeSet->getInvoiceUrl() !== null || $changeSet->getInvoiceNumber() !== null;
        $externalCodeChanged = $changeSet->getExternalCode() !== null;

        // Persist only what was changed:

        if ($amountChanged && !$this->orderStateManager->wasShipped($order)) {
            $this->updateOrderLimitsService->unlockLimits($orderContainer, $changeSet);
        }

        if ($amountChanged || $durationChanged) {
            $duration = $changeSet->getDuration() !== null
                ? $changeSet->getDuration()
                : $orderContainer->getOrderFinancialDetails()->getDuration();
            $this->financialDetailsPersistenceService->updateFinancialDetails(
                $orderContainer,
                $changeSet,
                $duration
            );
        }

        if ($invoiceChanged || $externalCodeChanged) {
            $this->updateOrder($orderContainer, $changeSet);
        }

        if ($invoiceChanged) {
            $this->updateInvoice($order);
        }

        if (($amountChanged || $invoiceChanged || $durationChanged) && $this->orderStateManager->wasShipped($order)) {
            $this->paymentsService->modifyOrder(
                $this->paymentRequestFactory->createModifyRequestDTO($orderContainer)
            );
        }

        return $changeSet;
    }

    private function updateOrder(OrderContainer $orderContainer, UpdateOrderRequest $changeSet)
    {
        $order = $orderContainer->getOrder();

        if ($changeSet->getExternalCode()) {
            $order->setExternalCode($changeSet->getExternalCode());
        }
        if ($changeSet->getInvoiceNumber()) {
            $order->setInvoiceNumber($changeSet->getInvoiceNumber());
        }
        if ($changeSet->getInvoiceUrl()) {
            $order->setInvoiceUrl($changeSet->getInvoiceUrl());
        }
        $this->orderRepository->update($order);
    }

    private function updateInvoice(OrderEntity $order)
    {
        try {
            $this->invoiceManager->upload($order, InvoiceUploadHandlerInterface::EVENT_UPDATE);
        } catch (OrderInvoiceUploadException $exception) {
            throw new UpdateOrderException("Order invoice cannot be updated: upload failed.", 0, $exception);
        }
    }
}
