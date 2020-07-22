<?php

declare(strict_types=1);

namespace App\Application\UseCase\UpdateOrderWithInvoice;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderBeingCollectedException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\Order\SalesforceInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use App\DomainModel\OrderUpdateWithInvoice\UpdateOrderWithInvoicePersistenceService;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class UpdateOrderWithInvoiceUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $orderContainerFactory;

    private $updateOrderWithInvoicePersistenceService;

    private $invoiceManager;

    private $salesforce;

    private $orderStateManager;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        UpdateOrderWithInvoicePersistenceService $updateOrderWithInvoicePersistenceService,
        OrderInvoiceManager $invoiceManager,
        SalesforceInterface $salesforce,
        OrderStateManager $orderStateManager
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->updateOrderWithInvoicePersistenceService = $updateOrderWithInvoicePersistenceService;
        $this->invoiceManager = $invoiceManager;
        $this->salesforce = $salesforce;
        $this->orderStateManager = $orderStateManager;
    }

    public function execute(UpdateOrderWithInvoiceRequest $request): void
    {
        $this->validateRequest($request);

        try {
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndUuid(
                $request->getMerchantId(),
                $request->getOrderId()
            );

            $order = $orderContainer->getOrder();
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        if ($this->isOrderAfterShipment($orderContainer)) {
            $this->validateRequest($request, null, ['InvoiceNumber']);

            if ($request->getInvoiceNumber() !== $order->getInvoiceNumber()) {
                $this->validateRequest($request, null, ['InvoiceFile']);
            }
        }

        if ($order->getMarkedAsFraudAt()) {
            throw new FraudOrderException();
        }

        if ($this->isOrderLateAndInCollections($order)) {
            throw new OrderBeingCollectedException();
        }

        $changes = $this->updateOrderWithInvoicePersistenceService->update(
            $orderContainer,
            $request
        );

        if ($this->isOrderAfterShipment($orderContainer) && $request->getInvoiceFile()) {
            $this->invoiceManager->uploadInvoiceFile(
                $order,
                $request->getInvoiceFile()
            );
        }

        $this->logInfo('Order updated, state {state}.', [
            'state' => $order->getState(),
            'amount_changed' => (int) $changes->getAmount() !== null,
        ]);
    }

    private function isOrderAfterShipment(OrderContainer $orderContainer): bool
    {
        return in_array(
            $orderContainer->getOrder()->getState(),
            [OrderStateManager::STATE_SHIPPED, OrderStateManager::STATE_PAID_OUT, OrderStateManager::STATE_LATE]
        );
    }

    private function isOrderLateAndInCollections(OrderEntity $order): bool
    {
        if (!$this->orderStateManager->isLate($order)) {
            return false;
        }

        return null !== $this->salesforce->getOrderCollectionsStatus($order->getUuid());
    }
}
