<?php

declare(strict_types=1);

namespace App\Application\UseCase\UpdateOrderWithInvoice;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderStateManager;
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

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        UpdateOrderWithInvoicePersistenceService $updateOrderWithInvoicePersistenceService,
        OrderInvoiceManager $invoiceManager
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->updateOrderWithInvoicePersistenceService = $updateOrderWithInvoicePersistenceService;
        $this->invoiceManager = $invoiceManager;
    }

    public function execute(UpdateOrderWithInvoiceRequest $request): void
    {
        $this->validateRequest($request);

        try {
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndUuid(
                $request->getMerchantId(),
                $request->getOrderId()
            );
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        if ($this->isOrderAfterShipment($orderContainer)) {
            $this->validateRequest($request, null, ['InvoiceNumber']);

            if ($request->getInvoiceNumber() !== $orderContainer->getOrder()->getInvoiceNumber()) {
                $this->validateRequest($request, null, ['InvoiceFile']);
            }
        }

        if ($orderContainer->getOrder()->getMarkedAsFraudAt()) {
            throw new FraudOrderException();
        }

        $changes = $this->updateOrderWithInvoicePersistenceService->update(
            $orderContainer,
            $request
        );

        if ($this->isOrderAfterShipment($orderContainer) && $request->getInvoiceFile()) {
            $this->invoiceManager->uploadInvoiceFile(
                $orderContainer->getOrder(),
                $request->getInvoiceFile()
            );
        }

        $this->logInfo('Order updated, state {state}.', [
            'state' => $orderContainer->getOrder()->getState(),
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
}
