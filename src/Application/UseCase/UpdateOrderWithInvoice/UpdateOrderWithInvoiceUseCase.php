<?php

declare(strict_types=1);

namespace App\Application\UseCase\UpdateOrderWithInvoice;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderBeingCollectedException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\SalesforceInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use App\DomainModel\OrderUpdateWithInvoice\UpdateOrderWithInvoicePersistenceService;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class UpdateOrderWithInvoiceUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private OrderContainerFactory $orderContainerFactory;

    private UpdateOrderWithInvoicePersistenceService $updateOrderWithInvoicePersistenceService;

    private OrderInvoiceManager $invoiceManager;

    private SalesforceInterface $salesforce;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        UpdateOrderWithInvoicePersistenceService $updateOrderWithInvoicePersistenceService,
        OrderInvoiceManager $invoiceManager,
        SalesforceInterface $salesforce
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->updateOrderWithInvoicePersistenceService = $updateOrderWithInvoicePersistenceService;
        $this->invoiceManager = $invoiceManager;
        $this->salesforce = $salesforce;
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

        if ($orderContainer->getOrder()->wasShipped()) {
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

        if ($request->getInvoiceFile() !== null && $orderContainer->getOrder()->wasShipped()) {
            $this->invoiceManager->uploadInvoiceFile(
                $order,
                $request->getInvoiceFile()
            );
        }

        $this->logInfo('Order updated, state {state}.', [
            LoggingInterface::KEY_NAME => $order->getState(),
            LoggingInterface::KEY_NUMBER => (int) $changes->getAmount() !== null,
        ]);
    }

    private function isOrderLateAndInCollections(OrderEntity $order): bool
    {
        if (!$order->isLate()) {
            return false;
        }

        return $this->salesforce->getOrderCollectionsStatus($order->getUuid()) !== null;
    }
}
