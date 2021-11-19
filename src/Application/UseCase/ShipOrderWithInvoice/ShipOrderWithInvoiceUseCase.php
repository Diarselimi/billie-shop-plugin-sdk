<?php

declare(strict_types=1);

namespace App\Application\UseCase\ShipOrderWithInvoice;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\CreateInvoice\CreateInvoiceRequest;
use App\Application\UseCase\ShipOrder\Exception\ShipOrderAmountExceededException;
use App\Application\UseCase\ShipOrder\Exception\ShipOrderMerchantFeeNotSetException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Fee\FeeCalculationException;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceFactory;
use App\DomainModel\Invoice\ShippingInfo\ShippingInfoRepository;
use App\DomainModel\Order\Lifecycle\ShipOrder\LegacyShipOrderService;
use App\DomainModel\Order\Lifecycle\ShipOrder\ShipOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderInvoiceDocument\InvoiceDocumentCreator;
use App\DomainModel\OrderResponse\LegacyOrderResponse;
use App\DomainModel\OrderResponse\LegacyOrderResponseFactory;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Workflow\Registry;

class ShipOrderWithInvoiceUseCase implements ValidatedUseCaseInterface, LoggingInterface
{
    use ValidatedUseCaseTrait,
        LoggingTrait;

    protected InvoiceDocumentCreator $invoiceManager;

    private OrderContainerFactory $orderContainerFactory;

    private Registry $workflowRegistry;

    private LegacyShipOrderService $legacyShipOrderService;

    private ShipOrderService $shipOrderService;

    private LegacyOrderResponseFactory $orderResponseFactory;

    private InvoiceFactory $invoiceFactory;

    private ShippingInfoRepository $shippingInfoRepository;

    public function __construct(
        InvoiceDocumentCreator $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        ShipOrderService $shipOrderService,
        Registry $workflowRegistry,
        LegacyOrderResponseFactory $orderResponseFactory,
        InvoiceFactory $invoiceFactory,
        ShippingInfoRepository $shippingInfoRepository
    ) {
        $this->invoiceManager = $invoiceManager;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->shipOrderService = $shipOrderService;
        $this->workflowRegistry = $workflowRegistry;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->invoiceFactory = $invoiceFactory;
        $this->shippingInfoRepository = $shippingInfoRepository;
    }

    public function execute(ShipOrderWithInvoiceRequest $request): LegacyOrderResponse
    {
        $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndUuid(
            $request->getMerchantId(),
            $request->getOrderId()
        );

        $this->validate($request, $orderContainer);

        $order = $orderContainer->getOrder();
        $invoice = $this->makeInvoice($orderContainer, $request);
        $this->addRequestDataToOrder($request, $order);

        $orderContainer->addInvoice($invoice);
        $this->shipOrderService->ship($orderContainer, $invoice);
        if ($request->getShippingInfo() !== null) {
            $this->shippingInfoRepository->save($invoice->getShippingInfo());
        }

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
                throw new ShipOrderAmountExceededException();
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

        $input = (new CreateInvoiceRequest($orderContainer->getOrder()->getMerchantId(), $request->getInvoiceUuid()))
            ->setAmount($amount)
            ->setExternalCode($request->getInvoiceNumber());

        try {
            $invoice = $this->invoiceFactory->create(
                $orderContainer,
                $input
            );
        } catch (FeeCalculationException $exception) {
            $this->logSuppressedException($exception, 'Merchant fee configuration is incorrect');

            throw new ShipOrderMerchantFeeNotSetException();
        }

        return $invoice;
    }
}
