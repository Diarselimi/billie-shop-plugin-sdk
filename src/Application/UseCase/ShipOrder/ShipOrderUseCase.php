<?php

declare(strict_types=1);

namespace App\Application\UseCase\ShipOrder;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Fee\FeeCalculationException;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceFactory;
use App\DomainModel\Order\Lifecycle\ShipOrder\ShipOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderInvoiceDocument\InvoiceDocumentUploadException;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\InvoiceDocumentUploadHandlerAggregator;
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

    private InvoiceDocumentUploadHandlerAggregator $invoiceManager;

    private OrderContainerFactory $orderContainerFactory;

    private Registry $workflowRegistry;

    private ShipOrderService $shipOrderService;

    private OrderResponseFactory $orderResponseFactory;

    private InvoiceFactory $invoiceFactory;

    public function __construct(
        InvoiceDocumentUploadHandlerAggregator $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        Registry $workflowRegistry,
        ShipOrderService $shipOrderService,
        OrderResponseFactory $orderResponseFactory,
        InvoiceFactory $invoiceFactory
    ) {
        $this->invoiceManager = $invoiceManager;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->workflowRegistry = $workflowRegistry;
        $this->shipOrderService = $shipOrderService;
        $this->orderResponseFactory = $orderResponseFactory;
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
        $invoice = $this->makeInvoice($orderContainer, $request);

        $this->logInfo('Ship order v2');
        $this->shipOrderService->ship($orderContainer, $invoice);

        $this->uploadInvoice($order, $request, $invoice->getUuid());

        return $this->orderResponseFactory->create($orderContainer);
    }

    private function uploadInvoice(OrderEntity $order, ShipOrderRequest $request, string $invoiceUuid): void
    {
        try {
            $this->invoiceManager->handle(
                $order,
                $invoiceUuid,
                $request->getInvoiceUrl(),
                $request->getInvoiceNumber(),
                InvoiceDocumentUploadHandlerAggregator::EVENT_SOURCE_SHIPMENT
            );
        } catch (InvoiceDocumentUploadException $exception) {
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

    private function makeInvoice(OrderContainer $orderContainer, ShipOrderRequest $request): Invoice
    {
        try {
            return $this->invoiceFactory->create(
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
    }
}
