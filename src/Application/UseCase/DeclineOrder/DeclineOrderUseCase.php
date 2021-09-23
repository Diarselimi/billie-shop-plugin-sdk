<?php

namespace App\Application\UseCase\DeclineOrder;

use App\Application\CommandHandler;
use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\DomainModel\Order\Lifecycle\DeclineOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Sepa\Client\DomainModel\SepaClientInterface;
use Ozean12\Support\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Component\Workflow\Registry;

class DeclineOrderUseCase implements LoggingInterface, CommandHandler
{
    use LoggingTrait;

    private Registry $workflowRegistry;

    private DeclineOrderService $declineOrderService;

    private OrderContainerFactory $orderContainerFactory;

    private SepaClientInterface $sepaClient;

    public function __construct(
        Registry $workflowRegistry,
        DeclineOrderService $declineOrderService,
        OrderContainerFactory $orderManagerFactory,
        SepaClientInterface $sepaClient
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->declineOrderService = $declineOrderService;
        $this->orderContainerFactory = $orderManagerFactory;
        $this->sepaClient = $sepaClient;
    }

    public function execute(DeclineOrderRequest $request): void
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadByUuid($request->getUuid());
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        $order = $orderContainer->getOrder();

        if (!$this->workflowRegistry->get($order)->can($order, OrderEntity::TRANSITION_DECLINE)) {
            throw new WorkflowException('Cannot decline the order. Order is in \'' . $order->getState() . '\' state.');
        }

        $this->declineOrderService->decline($orderContainer);

        if ($order->getDebtorSepaMandateUuid() === null) {
            return;
        }

        try {
            $this->sepaClient->revokeMandate($order->getDebtorSepaMandateUuid());
        } catch (HttpExceptionInterface $exception) {
            $this->logSuppressedException(
                $exception,
                sprintf('Mandate revoke call failed for uuid %s ', $order->getDebtorSepaMandateUuid())
            );
        }
    }
}
