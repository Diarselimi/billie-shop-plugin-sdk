<?php

declare(strict_types=1);

namespace App\Application\UseCase\ShipOrder;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ShipOrder\Exception\ShipOrderMerchantFeeNotSetException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Fee\FeeCalculationException;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceFactory;
use App\DomainModel\Order\Lifecycle\ShipOrder\LegacyShipOrderService;
use App\DomainModel\Order\Lifecycle\ShipOrder\ShipOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderResponse\LegacyOrderResponse;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\InvoiceDocumentUploadHandlerAggregator;
use App\DomainModel\OrderResponse\LegacyOrderResponseFactory;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Workflow\Registry;

class ShipOrderUseCaseV1 implements ValidatedUseCaseInterface, LoggingInterface
{
    use ValidatedUseCaseTrait,
        LoggingTrait;

    private InvoiceDocumentUploadHandlerAggregator $invoiceManager;

    private OrderContainerFactory $orderContainerFactory;

    private Registry $workflowRegistry;

    private ShipOrderService $shipOrderService;

    private LegacyShipOrderService $legacyShipOrderService;

    private LegacyOrderResponseFactory $orderResponseFactory;

    private InvoiceFactory $invoiceFactory;

    public function __construct(
        InvoiceDocumentUploadHandlerAggregator $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        Registry $workflowRegistry,
        ShipOrderService $shipOrderService,
        LegacyShipOrderService $legacyShipOrderService,
        LegacyOrderResponseFactory $orderResponseFactory,
        InvoiceFactory $invoiceFactory
    ) {
        $this->invoiceManager = $invoiceManager;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->workflowRegistry = $workflowRegistry;
        $this->shipOrderService = $shipOrderService;
        $this->legacyShipOrderService = $legacyShipOrderService;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->invoiceFactory = $invoiceFactory;
    }

    public function execute(ShipOrderRequestV1 $request): LegacyOrderResponse
    {
        $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalIdOrUuid(
            $request->getMerchantId(),
            $request->getOrderId()
        );
        $order = $orderContainer->getOrder();

        $this->validate($request, $orderContainer);
        $this->addRequestDataToOrder($request, $order);

        $invoice = $this->makeInvoice($orderContainer, $request);
        $this->ship($orderContainer, $invoice);

        $this->uploadInvoice($order, $request, $invoice->getUuid());

        return $this->orderResponseFactory->create($orderContainer);
    }

    private function uploadInvoice(OrderEntity $order, ShipOrderRequestV1 $request, string $invoiceUuid): void
    {
        $this->invoiceManager->handle(
            $order,
            $invoiceUuid,
            $request->getInvoiceUrl(),
            $request->getInvoiceNumber(),
            InvoiceDocumentUploadHandlerAggregator::EVENT_SOURCE_SHIPMENT
        );
    }

    private function validate(ShipOrderRequestV1 $request, OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();
        $validationGroups = $order->getExternalCode() ? ['Default'] : ['Default', 'RequiredExternalCode'];
        $this->validateRequest($request, null, $validationGroups);

        if ($order->isWorkflowV2()) {
            throw new WorkflowException('Order workflow is not supported by api v1');
        }

        $workflow = $this->workflowRegistry->get($order);
        if (!$workflow->can($order, OrderEntity::TRANSITION_SHIP)) {
            throw new WorkflowException();
        }
    }

    private function addRequestDataToOrder(ShipOrderRequestV1 $request, OrderEntity $order): void
    {
        if ($request->getExternalCode() && empty($order->getExternalCode())) {
            $order->setExternalCode($request->getExternalCode());
        }

        $order
            ->setInvoiceNumber($request->getInvoiceNumber())
            ->setInvoiceUrl($request->getInvoiceUrl())
            ->setProofOfDeliveryUrl($request->getShippingDocumentUrl());
    }

    private function makeInvoice(OrderContainer $orderContainer, ShipOrderRequestV1 $request): Invoice
    {
        $financialDetails = $orderContainer->getOrderFinancialDetails();

        try {
            $invoice = $this->invoiceFactory->create(
                $orderContainer,
                new TaxedMoney(
                    $financialDetails->getAmountGross(),
                    $financialDetails->getAmountNet(),
                    $financialDetails->getAmountTax()
                ),
                $orderContainer->getOrderFinancialDetails()->getDuration(),
                $request->getInvoiceNumber(),
                $request->getShippingDocumentUrl()
            );
        } catch (FeeCalculationException $exception) {
            $this->logSuppressedException($exception, 'Merchant fee configuration is incorrect');

            throw new ShipOrderMerchantFeeNotSetException();
        }

        return $invoice;
    }

    private function ship(OrderContainer $orderContainer, Invoice $invoice): void
    {
        $this->logInfo('Ship order v1 in paella'); // PRE-BUTLER HACK TODO (partial-activation) remove on migration
        $this->legacyShipOrderService->ship($orderContainer, $invoice);

        $this->logInfo('Ship order v1 in core');
        $this->shipOrderService->ship($orderContainer, $invoice);
    }
}
