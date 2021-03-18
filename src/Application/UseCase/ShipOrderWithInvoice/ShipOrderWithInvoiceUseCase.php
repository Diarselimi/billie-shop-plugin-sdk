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
use App\DomainModel\OrderInvoiceDocument\InvoiceDocumentCreator;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use App\DomainModel\ShipOrder\ShipOrderException;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Workflow\Registry;

class ShipOrderWithInvoiceUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait,
        LoggingTrait;

    protected InvoiceDocumentCreator $invoiceManager;

    private OrderContainerFactory $orderContainerFactory;

    private Registry $workflowRegistry;

    private LegacyShipOrderService $legacyShipOrderService;

    private ShipOrderService $shipOrderService;

    private OrderResponseFactory $orderResponseFactory;

    private InvoiceFactory $invoiceFactory;

    public function __construct(
        InvoiceDocumentCreator $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        LegacyShipOrderService $legacyShipOrderService,
        ShipOrderService $shipOrderService,
        Registry $workflowRegistry,
        OrderResponseFactory $orderResponseFactory,
        InvoiceFactory $invoiceFactory
    ) {
        $this->invoiceManager = $invoiceManager;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->legacyShipOrderService = $legacyShipOrderService;
        $this->shipOrderService = $shipOrderService;
        $this->workflowRegistry = $workflowRegistry;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->invoiceFactory = $invoiceFactory;
    }

    public function execute(ShipOrderWithInvoiceRequest $request): OrderResponse
    {
        $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndUuid(
            $request->getMerchantId(),
            $request->getOrderId()
        );

        $this->validate($request, $orderContainer);

        $order = $orderContainer->getOrder();
        $invoice = $this->makeInvoice($orderContainer, $request);
        $this->addRequestDataToOrder($request, $order);

        $this->legacyShipOrderService->ship($orderContainer, $invoice);
        $orderContainer->addInvoice($invoice);
        $this->shipOrderService->ship($orderContainer, $invoice);

        $this->invoiceManager->createFromUpload(
            $order->getId(),
            $invoice->getUuid(),
            $request->getInvoiceNumber(),
            $request->getInvoiceFile()
        );

        return $this->orderResponseFactory->create($orderContainer);
    }

    private function validate(ShipOrderWithInvoiceRequest $request, OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();
        $validationGroups = $order->getExternalCode() ? ['Default'] : ['Default', 'RequiredExternalCode'];
        $this->validateRequest($request, null, $validationGroups);
        $workflow = $this->workflowRegistry->get($order);

        if ($order->isWorkflowV1() && !$workflow->can($order, OrderEntity::TRANSITION_SHIP)) {
            throw new WorkflowException('Ship transition not supported');
        }

        if ($order->isWorkflowV2()) {
            if (
                !$workflow->can($order, OrderEntity::TRANSITION_SHIP_FULLY)
                && !$workflow->can($order, OrderEntity::TRANSITION_SHIP_PARTIALLY)
            ) {
                throw new WorkflowException('Order cannot be shipped.');
            }

            if (!$request->hasAmount()) {
                return;
            }

            $financialDetails = $orderContainer->getOrderFinancialDetails();
            if ($request->getAmount()->getGross()->greaterThan($financialDetails->getUnshippedAmountGross())
                || $request->getAmount()->getNet()->greaterThan($financialDetails->getUnshippedAmountNet())
                || $request->getAmount()->getTax()->greaterThan($financialDetails->getUnshippedAmountTax())
            ) {
                throw new ShipOrderException('Requested amount exceeds order unshipped amount');
            }
        }
    }

    private function addRequestDataToOrder(ShipOrderWithInvoiceRequest $request, OrderEntity $order): void
    {
        if (!empty($request->getExternalCode()) && empty($order->getExternalCode())) {
            $order->setExternalCode($request->getExternalCode());
        }

        if ($order->isWorkflowV1()) {
            $order->setInvoiceNumber($request->getInvoiceNumber());
        }
    }

    private function makeInvoice(OrderContainer $orderContainer, ShipOrderWithInvoiceRequest $request): Invoice
    {
        $financialDetails = $orderContainer->getOrderFinancialDetails();
        $amount = $orderContainer->getOrder()->isWorkflowV2() && $request->getAmount() !== null
            ? $request->getAmount()
            : new TaxedMoney(
                $financialDetails->getAmountGross(),
                $financialDetails->getAmountNet(),
                $financialDetails->getAmountTax()
            );

        try {
            $invoice = $this->invoiceFactory->create(
                $orderContainer,
                $amount,
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
}
