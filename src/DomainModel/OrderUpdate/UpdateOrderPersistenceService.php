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

    private $orderFinancialDetailsFactory;

    private $orderFinancialDetailsRepository;

    private $merchantRepository;

    private $merchantDebtorLimitsService;

    private $paymentRequestFactory;

    private $updateOrderRequestValidator;

    public function __construct(
        PaymentsServiceInterface $paymentsService,
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        OrderFinancialDetailsFactory $orderFinancialDetailsFactory,
        OrderFinancialDetailsRepositoryInterface $orderFinancialDetailsRepository,
        OrderInvoiceManager $invoiceManager,
        PaymentRequestFactory $paymentRequestFactory,
        MerchantRepositoryInterface $merchantRepository,
        MerchantDebtorLimitsService $merchantDebtorLimitsService,
        UpdateOrderRequestValidator $updateOrderRequestValidator
    ) {
        $this->paymentsService = $paymentsService;
        $this->orderRepository = $orderRepository;
        $this->orderStateManager = $orderStateManager;
        $this->orderFinancialDetailsFactory = $orderFinancialDetailsFactory;
        $this->orderFinancialDetailsRepository = $orderFinancialDetailsRepository;
        $this->invoiceManager = $invoiceManager;
        $this->paymentRequestFactory = $paymentRequestFactory;
        $this->merchantRepository = $merchantRepository;
        $this->merchantDebtorLimitsService = $merchantDebtorLimitsService;
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
            $this->unlockLimits($orderContainer, $changeSet);
        }

        if ($amountChanged || $durationChanged) {
            $this->updateFinancialDetails($orderContainer, $changeSet);
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

    private function unlockLimits(OrderContainer $orderContainer, UpdateOrderRequest $changeSet)
    {
        $amountGrossDiff = $orderContainer->getOrderFinancialDetails()->getAmountGross()
            ->subtract($changeSet->getAmount()->getGross());

        // unlock merchant-debtor limit
        $this->merchantDebtorLimitsService->unlock($orderContainer, $amountGrossDiff);

        // unlock merchant limit
        $merchant = $orderContainer->getMerchant();
        $merchant->increaseFinancingLimit($amountGrossDiff);
        $this->merchantRepository->update($merchant);
    }

    private function updateFinancialDetails(OrderContainer $orderContainer, UpdateOrderRequest $changeSet)
    {
        $financialDetails = $orderContainer->getOrderFinancialDetails();

        if ($changeSet->getAmount() !== null) {
            $gross = $changeSet->getAmount()->getGross()->getMoneyValue();
            $net = $changeSet->getAmount()->getNet()->getMoneyValue();
            $tax = $changeSet->getAmount()->getTax()->getMoneyValue();
        } else {
            $gross = $financialDetails->getAmountGross()->getMoneyValue();
            $net = $financialDetails->getAmountNet()->getMoneyValue();
            $tax = $financialDetails->getAmountTax()->getMoneyValue();
        }

        $duration = $changeSet->getDuration() !== null ? $changeSet->getDuration() : $financialDetails->getDuration();

        $newFinancialDetails = $this->orderFinancialDetailsFactory
            ->create($financialDetails->getOrderId(), $gross, $net, $tax, $duration);

        $this->orderFinancialDetailsRepository->insert($newFinancialDetails);
        $orderContainer->setOrderFinancialDetails($newFinancialDetails);
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
