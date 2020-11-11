<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\DomainModel\Order\IdentifyAndTriggerAsyncIdentification;
use App\DomainModel\Order\Lifecycle\ApproveOrderService;
use App\DomainModel\Order\Lifecycle\DeclineOrderService;
use App\DomainModel\Order\Lifecycle\WaitingOrderService;
use App\DomainModel\Order\NewOrder\OrderPersistenceService;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use Symfony\Component\Workflow\Registry;

trait OrderCreationUseCaseTrait
{
    private OrderPersistenceService $orderPersistenceService;

    private OrderContainerFactory $orderContainerFactory;

    private OrderChecksRunnerService $orderChecksRunnerService;

    private OrderRepositoryInterface $orderRepository;

    private Registry $workflowRegistry;

    private ApproveOrderService $approveOrderService;

    private DeclineOrderService $declineOrderService;

    private WaitingOrderService $waitingOrderService;

    private IdentifyAndTriggerAsyncIdentification $identifyAndTriggerAsyncIdentification;

    private OrderResponseFactory $orderResponseFactory;

    private function createIdentifiedOrder(CreateOrderRequest $request): OrderContainer
    {
        $orderCreationDTO = $this->orderPersistenceService->persistFromRequest($request);
        $orderContainer = $this->orderContainerFactory->createFromNewOrderDTO($orderCreationDTO);

        if (!$this->orderChecksRunnerService->passesPreIdentificationChecks($orderContainer)) {
            $this->declineOrderService->decline($orderContainer);

            return $orderContainer;
        }

        if ($this->identifyAndTriggerAsyncIdentification->identifyDebtor($orderContainer)) {
            $this->orderRepository->updateMerchantDebtor(
                $orderContainer->getOrder()->getId(),
                $orderContainer->getMerchantDebtor()->getId()
            );
        }

        if (!$this->orderChecksRunnerService->passesPostIdentificationChecks($orderContainer)) {
            $this->declineOrderService->decline($orderContainer);
        }

        return $orderContainer;
    }
}
