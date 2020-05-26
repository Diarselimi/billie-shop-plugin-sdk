<?php

namespace App\Application\UseCase\UpdateOrderWithInvoice;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\OrderUpdateWithInvoice\UpdateOrderWithInvoicePersistenceService;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class UpdateOrderWithInvoiceUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $orderContainerFactory;

    private $updateOrderWithInvoicePersistenceService;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        UpdateOrderWithInvoicePersistenceService $updateOrderWithInvoicePersistenceService
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->updateOrderWithInvoicePersistenceService = $updateOrderWithInvoicePersistenceService;
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

        if ($orderContainer->getOrder()->getMarkedAsFraudAt()) {
            throw new FraudOrderException();
        }

        $changes = $this->updateOrderWithInvoicePersistenceService->update(
            $orderContainer,
            $request
        );

        $this->logInfo('Order updated, state {state}.', [
            'state' => $orderContainer->getOrder()->getState(),
            'amount_changed' => (int) $changes->getAmount() !== null,
        ]);
    }
}
