<?php

declare(strict_types=1);

namespace App\Application\UseCase\ShipOrder;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\FeatureFlag\FeatureFlagManager;
use App\DomainModel\Fee\FeeCalculationException;
use App\DomainModel\Invoice\InvoiceFactory;
use App\DomainModel\Order\Lifecycle\ShipOrder\ShipOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderInvoice\InvoiceUploadHandlerInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use App\DomainModel\OrderInvoice\OrderInvoiceUploadException;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use App\DomainModel\ShipOrder\ShipOrderException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Registry;

class ShipOrderUseCase implements ValidatedUseCaseInterface, LoggingInterface
{
    use ValidatedUseCaseTrait,
        LoggingTrait;

    private OrderInvoiceManager $invoiceManager;

    private OrderContainerFactory $orderContainerFactory;

    private Registry $workflowRegistry;

    private ShipOrderService $shipOrderService;

    private OrderResponseFactory $orderResponseFactory;

    private InvoiceFactory $invoiceFactory;

    private FeatureFlagManager $featureFlagManager;

    public function __construct(
        OrderInvoiceManager $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        Registry $workflowRegistry,
        ShipOrderService $shipOrderService,
        OrderResponseFactory $orderResponseFactory,
        FeatureFlagManager $featureFlagManager,
        InvoiceFactory $invoiceFactory
    ) {
        $this->invoiceManager = $invoiceManager;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->workflowRegistry = $workflowRegistry;
        $this->shipOrderService = $shipOrderService;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->featureFlagManager = $featureFlagManager;
        $this->invoiceFactory = $invoiceFactory;
    }

    public function execute(ShipOrderRequest $request): OrderResponse
    {
        $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalIdOrUuid(
            $request->getMerchantId(),
            $request->getOrderId()
        );
        $order = $orderContainer->getOrder();

        $this->validate($request, $orderContainer);
        $this->uploadInvoice($order, $request);
        $this->ship($orderContainer, $request);

        return $this->orderResponseFactory->create($orderContainer);
    }

    private function uploadInvoice(OrderEntity $order, ShipOrderRequest $request): void
    {
        try {
            $this->invoiceManager->upload($order, $request->getInvoiceUrl(), $request->getInvoiceNumber(), InvoiceUploadHandlerInterface::EVENT_SHIPMENT);
        } catch (OrderInvoiceUploadException $exception) {
            throw new ShipOrderException("Invoice can't be scheduled for upload", 0, $exception);
        }
    }

    private function validate(ShipOrderRequest $request, OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();
        $this->validateRequest($request, null, ['Default']);

        if (empty($order->getExternalCode())) {
            throw new ShipOrderException('Order id is not set');
        }

        if ($order->isWorkflowV1()) {
            throw new WorkflowException('Order workflow is not supported by api v2');
        }

        if (!$this->featureFlagManager->isEnabled(FeatureFlagManager::FEATURE_INVOICE_BUTLER)) {
            throw new ShipOrderException('Shipment v2 is supported only with invoice butler');
        }

        $workflow = $this->workflowRegistry->get($order);
        if (!$workflow->can($order, OrderEntity::TRANSITION_SHIP_FULLY)
            && !$workflow->can($order, OrderEntity::TRANSITION_SHIP_PARTIALLY)) {
            throw new WorkflowException('Order cannot be shipped.');
        }

        $financialDetails = $orderContainer->getOrderFinancialDetails();
        if ($request->getAmount()->getGross()->greaterThan($financialDetails->getUnshippedAmountGross())
            || $request->getAmount()->getNet()->greaterThan($financialDetails->getUnshippedAmountNet())
            || $request->getAmount()->getTax()->greaterThan($financialDetails->getUnshippedAmountTax())
        ) {
            throw new ShipOrderException('Requested amount exceeds order unshipped amount');
        }
    }

    private function ship(OrderContainer $orderContainer, ShipOrderRequest $request): void
    {
        try {
            $invoice = $this->invoiceFactory->create(
                $orderContainer,
                $request->getAmount(),
                $request->getDuration() ?? $orderContainer->getOrderFinancialDetails()->getDuration(),
                $request->getInvoiceNumber(),
                $request->getShippingDocumentUrl()
            );
        } catch (FeeCalculationException $exception) {
            $this->logSuppressedException($exception, 'Merchant fee configuration is incorrect');

            throw new ShipOrderException("Configuration isn't properly set");
        }

        $this->logInfo('Ship order v2 with butler');
        $this->shipOrderService->ship($orderContainer, $invoice);
    }
}
