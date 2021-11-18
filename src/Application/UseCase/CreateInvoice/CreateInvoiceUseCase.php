<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateInvoice;

use App\Application\CommandHandler;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ShipOrder\Exception\ShipOrderAmountExceededException;
use App\Application\UseCase\ShipOrder\Exception\ShipOrderMerchantFeeNotSetException;
use App\Application\UseCase\ShipOrder\Exception\ShipOrderNoOrderUuidException;
use App\Application\UseCase\ShipOrder\Exception\ShipOrderOrderExternalCodeNotSetException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Fee\FeeCalculationException;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceFactory;
use App\DomainModel\Invoice\ShippingInfo\ShippingInfoRepository;
use App\DomainModel\Order\Lifecycle\ShipOrder\ShipOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\InvoiceDocumentUploadHandlerAggregator;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Registry;

class CreateInvoiceUseCase implements ValidatedUseCaseInterface, LoggingInterface, CommandHandler
{
    use ValidatedUseCaseTrait,
        LoggingTrait;

    private InvoiceDocumentUploadHandlerAggregator $invoiceManager;

    private OrderContainerFactory $orderContainerFactory;

    private Registry $workflowRegistry;

    private ShipOrderService $shipOrderService;

    private InvoiceFactory $invoiceFactory;

    private ShippingInfoRepository $shippingInfoRepository;

    public function __construct(
        InvoiceDocumentUploadHandlerAggregator $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        Registry $workflowRegistry,
        ShipOrderService $shipOrderService,
        InvoiceFactory $invoiceFactory,
        ShippingInfoRepository $shippingInfoRepository
    ) {
        $this->invoiceManager = $invoiceManager;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->workflowRegistry = $workflowRegistry;
        $this->shipOrderService = $shipOrderService;
        $this->invoiceFactory = $invoiceFactory;
        $this->shippingInfoRepository = $shippingInfoRepository;
    }

    public function execute(CreateInvoiceRequest $request): void
    {
        $orders = $request->getOrders();
        if (count($orders) !== 1) {
            throw new ShipOrderNoOrderUuidException();
        }

        $orderId = reset($orders);

        $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalIdOrUuid(
            $request->getMerchantId(),
            $orderId
        );
        $order = $orderContainer->getOrder();

        $this->validate($request, $orderContainer);
        $invoice = $this->makeInvoice($orderContainer, $request);

        $this->logInfo('Ship order v2');
        $this->shipOrderService->ship($orderContainer, $invoice);
        $this->shippingInfoRepository->save($invoice);

        $this->uploadInvoice($order, $request, $invoice->getUuid());
    }

    private function uploadInvoice(OrderEntity $order, CreateInvoiceRequest $request, string $invoiceUuid): void
    {
        $this->invoiceManager->handle(
            $order,
            $invoiceUuid,
            $request->getInvoiceUrl(),
            $request->getExternalCode(),
            InvoiceDocumentUploadHandlerAggregator::EVENT_SOURCE_SHIPMENT
        );
    }

    private function validate(CreateInvoiceRequest $request, OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();
        $this->validateRequest($request, null, ['Default']);

        if (empty($order->getExternalCode())) {
            throw new ShipOrderOrderExternalCodeNotSetException();
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
            throw new ShipOrderAmountExceededException();
        }
    }

    private function makeInvoice(OrderContainer $orderContainer, CreateInvoiceRequest $request): Invoice
    {
        try {
            return $this->invoiceFactory->create(
                $orderContainer,
                $request
            );
        } catch (FeeCalculationException $exception) {
            $this->logSuppressedException($exception, 'Merchant fee configuration is incorrect');

            throw new ShipOrderMerchantFeeNotSetException();
        }
    }
}
