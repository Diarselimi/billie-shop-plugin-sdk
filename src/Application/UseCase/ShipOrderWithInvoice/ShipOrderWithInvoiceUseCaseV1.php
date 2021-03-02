<?php

declare(strict_types=1);

namespace App\Application\UseCase\ShipOrderWithInvoice;

use App\Application\Exception\WorkflowException;
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
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderInvoiceDocument\InvoiceDocumentCreator;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use App\DomainModel\ShipOrder\ShipOrderException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Workflow\Registry;

class ShipOrderWithInvoiceUseCaseV1 implements ValidatedUseCaseInterface, LoggingInterface
{
    use ValidatedUseCaseTrait,
        LoggingTrait;

    protected InvoiceDocumentCreator $invoiceManager;

    private OrderContainerFactory $orderContainerFactory;

    private Registry $workflowRegistry;

    private LegacyShipOrderService $legacyShipOrderService;

    private OrderResponseFactory $orderResponseFactory;

    private ShipOrderService $shipOrderService;

    private InvoiceFactory $invoiceFactory;

    public function __construct(
        InvoiceDocumentCreator $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        LegacyShipOrderService $legacyShipOrderService,
        Registry $workflowRegistry,
        OrderResponseFactory $orderResponseFactory,
        ShipOrderService $shipOrderService,
        InvoiceFactory $invoiceFactory
    ) {
        $this->invoiceManager = $invoiceManager;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->legacyShipOrderService = $legacyShipOrderService;
        $this->workflowRegistry = $workflowRegistry;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->shipOrderService = $shipOrderService;
        $this->invoiceFactory = $invoiceFactory;
    }

    public function execute(ShipOrderWithInvoiceRequestV1 $request): OrderResponse
    {
        $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndUuid(
            $request->getMerchantId(),
            $request->getOrderId()
        );
        $order = $orderContainer->getOrder();

        $this->validate($request, $orderContainer);
        $this->addRequestDataToOrder($request, $order);

        $invoice = $this->makeInvoice($orderContainer, $request);
        $this->ship($orderContainer, $invoice);

        $this->invoiceManager->createFromUpload(
            $order->getId(),
            $invoice->getUuid(),
            $request->getInvoiceNumber(),
            $request->getInvoiceFile()
        );

        return $this->orderResponseFactory->create($orderContainer);
    }

    private function validate(ShipOrderWithInvoiceRequestV1 $request, OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();
        $validationGroups = $order->getExternalCode() ? ['Default'] : ['Default', 'RequiredExternalCode'];
        $this->validateRequest($request, null, $validationGroups);

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

    private function makeInvoice(OrderContainer $orderContainer, ShipOrderWithInvoiceRequestV1 $request): Invoice
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
                null
            );
        } catch (FeeCalculationException $exception) {
            $this->logSuppressedException($exception, 'Merchant fee configuration is incorrect');

            throw new ShipOrderException("Configuration isn't properly set");
        }

        return $invoice;
    }

    private function ship(OrderContainer $orderContainer, Invoice $invoice): void
    {
        $this->logInfo('Ship order v1 in paella'); // PRE-BUTLER HACK TODO (partial-activation) remove on migration
        $this->legacyShipOrderService->ship(
            $orderContainer,
            $invoice
        );

        $orderContainer->addInvoice($invoice);
        $this->logInfo('Ship order v1 in core');
        $this->shipOrderService->ship($orderContainer, $invoice);
    }
}
