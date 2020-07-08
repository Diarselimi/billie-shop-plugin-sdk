<?php

namespace App\Application\UseCase\UpdateOrder;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\OrderUpdate\UpdateOrderPersistenceService;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class UpdateOrderUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $orderContainerFactory;

    private $updateOrderPersistenceService;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        UpdateOrderPersistenceService $updateOrderPersistenceService
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->updateOrderPersistenceService = $updateOrderPersistenceService;
    }

    public function execute(UpdateOrderRequest $request): void
    {
        $this->validateRequest($request);

        if ($request->getExternalCode() === '') {
            $this->logInfo('[test] Update order empty external code');
        }

        try {
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalIdOrUuid(
                $request->getMerchantId(),
                $request->getOrderId()
            );
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        if ($orderContainer->getOrder()->getMarkedAsFraudAt()) {
            throw new FraudOrderException();
        }

        $changes = $this->updateOrderPersistenceService->update($orderContainer, $request);

        $this->logInfo('Start order update, state {state}.', [
            'state' => $orderContainer->getOrder()->getState(),
            'duration_changed' => (int) $changes->getDuration() !== null,
            'invoice_changed' => (int) ($changes->getInvoiceNumber() !== null) || ($changes->getInvoiceUrl() !== null),
            'amount_changed' => (int) $changes->getAmount() !== null,
            'external_code_changed' => (int) $changes->getExternalCode() !== null,
        ]);
    }
}
