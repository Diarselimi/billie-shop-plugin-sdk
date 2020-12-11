<?php

declare(strict_types=1);

namespace App\Application\UseCase\ShipOrder;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\FeatureFlag\FeatureFlagManager;
use App\DomainModel\Fee\FeeCalculationException;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceFactory;
use App\DomainModel\Order\Lifecycle\ShipOrder\LegacyShipOrderService;
use App\DomainModel\Order\Lifecycle\ShipOrder\ShipOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderInvoice\InvoiceUploadHandlerInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use App\DomainModel\OrderInvoice\OrderInvoiceUploadException;
use App\DomainModel\OrderResponse\OrderResponseV1;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use App\DomainModel\ShipOrder\ShipOrderException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Workflow\Registry;

class ShipOrderUseCaseV1 implements ValidatedUseCaseInterface, LoggingInterface
{
    use ValidatedUseCaseTrait,
        LoggingTrait;

    private OrderInvoiceManager $invoiceManager;

    private OrderContainerFactory $orderContainerFactory;

    private Registry $workflowRegistry;

    private ShipOrderService $shipOrderService;

    private LegacyShipOrderService $legacyShipOrderService;

    private OrderResponseFactory $orderResponseFactory;

    private InvoiceFactory $invoiceFactory;

    private FeatureFlagManager $featureFlagManager;

    public function __construct(
        OrderInvoiceManager $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        Registry $workflowRegistry,
        ShipOrderService $shipOrderService,
        LegacyShipOrderService $legacyShipOrderService,
        OrderResponseFactory $orderResponseFactory,
        FeatureFlagManager $featureFlagManager,
        InvoiceFactory $invoiceFactory
    ) {
        $this->invoiceManager = $invoiceManager;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->workflowRegistry = $workflowRegistry;
        $this->shipOrderService = $shipOrderService;
        $this->legacyShipOrderService = $legacyShipOrderService;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->featureFlagManager = $featureFlagManager;
        $this->invoiceFactory = $invoiceFactory;
    }

    public function execute(ShipOrderRequestV1 $request): OrderResponseV1
    {
        $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalIdOrUuid(
            $request->getMerchantId(),
            $request->getOrderId()
        );
        $order = $orderContainer->getOrder();

        $this->validate($request, $orderContainer);
        $this->addRequestDataToOrder($request, $order);
        $this->ship($orderContainer, $request);
        $this->uploadInvoice($order);

        return $this->orderResponseFactory->createV1($orderContainer);
    }

    private function uploadInvoice(OrderEntity $order): void
    {
        try {
            $this->invoiceManager->upload($order, $order->getInvoiceUrl(), $order->getInvoiceNumber(), InvoiceUploadHandlerInterface::EVENT_SHIPMENT);
        } catch (OrderInvoiceUploadException $exception) {
            throw new ShipOrderException("Invoice can't be scheduled for upload", 0, $exception);
        }
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
            ->setProofOfDeliveryUrl($request->getShippingDocumentUrl())
        ;
    }

    private function ship(OrderContainer $orderContainer, ShipOrderRequestV1 $request): void
    {
        if ($this->featureFlagManager->isEnabled(FeatureFlagManager::FEATURE_INVOICE_BUTLER)) {
            $financialDetails = $orderContainer->getOrderFinancialDetails();

            try {
                $invoice = $this->invoiceFactory->create(
                    $orderContainer,
                    new TaxedMoney($financialDetails->getAmountGross(), $financialDetails->getAmountNet(), $financialDetails->getAmountTax()),
                    $orderContainer->getOrderFinancialDetails()->getDuration(),
                    $request->getInvoiceNumber(),
                    $request->getShippingDocumentUrl()
                );
            } catch (FeeCalculationException $exception) {
                $this->logSuppressedException($exception, 'Merchant fee configuration is incorrect');

                throw new ShipOrderException("Configuration isn't properly set");
            }

            $this->logInfo('Ship order v1 with butler');
            $this->shipOrderService->ship($orderContainer, $invoice);
        } else {
            $this->logInfo('Ship order v1 without butler');
            $this->legacyShipOrderService->ship($orderContainer, new Invoice());
        }
    }
}
