<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateInvoice;

use App\Application\CommandHandler;
use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ShipOrder\Exception\ShipOrderAmountExceededException;
use App\Application\UseCase\ShipOrder\Exception\ShipOrderMerchantFeeNotSetException;
use App\DomainModel\Fee\FeeCalculationException;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceFactory;
use App\DomainModel\Invoice\ShippingInfo\ShippingInfoRepository;
use App\DomainModel\Order\Lifecycle\ShipOrder\ShipOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use Symfony\Component\Workflow\Registry;

/**
 * This handler Creates invoice for a merchant but we don't get an invoice for the buyer/debtor
 * This handler will replace the CreateInvoiceUseCase we need to handle invoice updating async
 * Rename shipping to capturing the money or come up with a different name for our domain.
 * //TODO
 */
class CreateInvoiceCommandHandler implements CommandHandler
{
    private Registry $workflowRegistry;

    private OrderContainerFactory $orderContainerFactory;

    private ShipOrderService $shipOrderService;

    private InvoiceFactory $invoiceFactory;

    private ShippingInfoRepository $shippingInfoRepository;

    public function __construct(
        Registry $workflowRegistry,
        OrderContainerFactory $orderContainerFactory,
        ShipOrderService $shipOrderService,
        InvoiceFactory $invoiceFactory,
        ShippingInfoRepository $shippingInfoRepository
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->shipOrderService = $shipOrderService;
        $this->invoiceFactory = $invoiceFactory;
        $this->shippingInfoRepository = $shippingInfoRepository;
    }

    public function execute(CreateInvoiceCommand $input): void
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadByUuid($input->getOrderId());
        } catch (OrderContainerFactoryException $ex) {
            throw new OrderNotFoundException();
        }

        $order = $orderContainer->getOrder();

        if (!$this->isOrderInTheSupportedState($order)) {
            throw new WorkflowException('Order cannot be shipped.');
        }

        if ($input->getAmount()->getGross()->greaterThan($orderContainer->getOrderFinancialDetails()->getUnshippedAmountGross())) {
            throw new ShipOrderAmountExceededException();
        }

        $invoice = $this->makeInvoice($orderContainer, $input);
        $this->shipOrderService->ship($orderContainer, $invoice);
        if ($input->getShippingInfo() !== null) {
            $this->shippingInfoRepository->save($invoice->getShippingInfo());
        }
    }

    private function isOrderInTheSupportedState(OrderEntity $order): bool
    {
        $workflow = $this->workflowRegistry->get($order);

        return $workflow->can($order, OrderEntity::TRANSITION_SHIP_FULLY)
            || $workflow->can($order, OrderEntity::TRANSITION_SHIP_PARTIALLY);
    }

    private function makeInvoice(OrderContainer $orderContainer, CreateInvoiceCommand $request): Invoice
    {
        try {
            return $this->invoiceFactory->create(
                $orderContainer,
                $request
            );
        } catch (FeeCalculationException $exception) {
            throw new ShipOrderMerchantFeeNotSetException();
        }
    }
}
