<?php

declare(strict_types=1);

namespace App\Application\UseCase\ShipOrderWithInvoice;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\FeatureFlag\FeatureFlagManager;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\Lifecycle\ShipOrder\LegacyShipOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use App\DomainModel\OrderResponse\OrderResponseV1;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use App\DomainModel\ShipOrder\ShipOrderException;
use Symfony\Component\Workflow\Registry;

class ShipOrderWithInvoiceUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    protected OrderInvoiceManager $invoiceManager;

    private OrderContainerFactory $orderContainerFactory;

    private Registry $workflowRegistry;

    private LegacyShipOrderService $legacyShipOrderService;

    private OrderResponseFactory $orderResponseFactory;

    private FeatureFlagManager $featureFlagManager;

    public function __construct(
        OrderInvoiceManager $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        LegacyShipOrderService $legacyShipOrderService,
        Registry $workflowRegistry,
        OrderResponseFactory $orderResponseFactory,
        FeatureFlagManager $featureFlagManager
    ) {
        $this->invoiceManager = $invoiceManager;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->legacyShipOrderService = $legacyShipOrderService;
        $this->workflowRegistry = $workflowRegistry;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->featureFlagManager = $featureFlagManager;
    }

    public function execute(ShipOrderWithInvoiceRequestV1 $request): OrderResponseV1
    {
        $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndUuid(
            $request->getMerchantId(),
            $request->getOrderId()
        );
        $order = $orderContainer->getOrder();

        $this->validate($request, $orderContainer);
        $this->addRequestDataToOrder($request, $order);
        $this->uploadInvoice($request, $order);
        $this->ship($orderContainer, $request);

        return $this->orderResponseFactory->createV1($orderContainer);
    }

    private function uploadInvoice(ShipOrderWithInvoiceRequestV1 $request, OrderEntity $order): void
    {
        $this->invoiceManager->uploadInvoiceFile($order, $request->getInvoiceFile());
    }

    private function validate(ShipOrderWithInvoiceRequestV1 $request, OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();
        $validationGroups = $order->getExternalCode() ? ['Default'] : ['Default', 'RequiredExternalCode'];
        $this->validateRequest($request, null, $validationGroups);

        if ($this->featureFlagManager->isEnabled(FeatureFlagManager::FEATURE_INVOICE_BUTLER)) {
            throw new ShipOrderException('Dashboard shipment v2 is not supported with invoice butler');
        }

        if ($order->isWorkflowV2()) {
            throw new WorkflowException('Order workflow is not supported by api v1');
        }

        $workflow = $this->workflowRegistry->get($order);
        if (!$workflow->can($order, OrderEntity::TRANSITION_SHIP)) {
            throw new WorkflowException('Ship transition not supported');
        }
    }

    private function addRequestDataToOrder(ShipOrderWithInvoiceRequestV1 $request, OrderEntity $order): void
    {
        if (!empty($request->getExternalCode()) && empty($order->getExternalCode())) {
            $order->setExternalCode($request->getExternalCode());
        }

        $order->setInvoiceNumber($request->getInvoiceNumber());
    }

    private function ship(OrderContainer $orderContainer, ShipOrderWithInvoiceRequestV1 $request): void
    {
        $this->legacyShipOrderService->ship($orderContainer, new Invoice());
    }
}
