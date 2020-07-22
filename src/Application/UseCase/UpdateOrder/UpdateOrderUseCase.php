<?php

namespace App\Application\UseCase\UpdateOrder;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderBeingCollectedException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\Order\SalesforceInterface;
use App\DomainModel\OrderUpdate\UpdateOrderPersistenceService;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class UpdateOrderUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $orderContainerFactory;

    private $updateOrderPersistenceService;

    private $salesforce;

    private $orderStateManager;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        UpdateOrderPersistenceService $updateOrderPersistenceService,
        SalesforceInterface $salesforce,
        OrderStateManager $orderStateManager
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->updateOrderPersistenceService = $updateOrderPersistenceService;
        $this->salesforce = $salesforce;
        $this->orderStateManager = $orderStateManager;
    }

    public function execute(UpdateOrderRequest $request): void
    {
        $this->validateRequest($request);

        try {
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalIdOrUuid(
                $request->getMerchantId(),
                $request->getOrderId()
            );

            $order = $orderContainer->getOrder();
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        if ($order->getMarkedAsFraudAt()) {
            throw new FraudOrderException();
        }

        if ($this->isOrderLateAndInCollections($order)) {
            throw new OrderBeingCollectedException();
        }

        $changes = $this->updateOrderPersistenceService->update($orderContainer, $request);

        $this->logInfo('Start order update, state {state}.', [
            'state' => $order->getState(),
            'duration_changed' => (int) $changes->getDuration() !== null,
            'invoice_changed' => (int) ($changes->getInvoiceNumber() !== null) || ($changes->getInvoiceUrl() !== null),
            'amount_changed' => (int) $changes->getAmount() !== null,
            'external_code_changed' => (int) $changes->getExternalCode() !== null,
        ]);
    }

    private function isOrderLateAndInCollections(OrderEntity $order): bool
    {
        if (!$this->orderStateManager->isLate($order)) {
            return false;
        }

        return null !== $this->salesforce->getOrderCollectionsStatus($order->getUuid());
    }
}
