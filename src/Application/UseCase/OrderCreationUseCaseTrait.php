<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\DomainModel\Order\IdentifyAndTriggerAsyncIdentification;
use App\DomainModel\Order\NewOrder\OrderPersistenceService;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderResponse\OrderResponseFactory;

trait OrderCreationUseCaseTrait
{
    /**
     * @var OrderPersistenceService
     */
    private $persistNewOrderService;

    /**
     * @var OrderContainerFactory
     */
    private $orderContainerFactory;

    /**
     * @var OrderChecksRunnerService
     */
    private $orderChecksRunnerService;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderStateManager
     */
    private $orderStateManager;

    /**
     * @var IdentifyAndTriggerAsyncIdentification
     */
    private $identifyAndTriggerAsyncIdentification;

    /**
     * @var OrderResponseFactory
     */
    private $orderResponseFactory;

    private function createIdentifiedOrder(CreateOrderRequest $request): OrderContainer
    {
        $orderCreationDTO = $this->persistNewOrderService->persistFromRequest($request);
        $orderContainer = $this->orderContainerFactory->createFromNewOrderDTO($orderCreationDTO);

        if (!$this->orderChecksRunnerService->passesPreIdentificationChecks($orderContainer)) {
            $this->orderStateManager->decline($orderContainer);

            return $orderContainer;
        }

        if ($this->identifyAndTriggerAsyncIdentification->identifyDebtor($orderContainer)) {
            $this->orderRepository->updateMerchantDebtor(
                $orderContainer->getOrder()->getId(),
                $orderContainer->getMerchantDebtor()->getId()
            );
        }

        if (!$this->orderChecksRunnerService->passesPostIdentificationChecks($orderContainer)) {
            $this->orderStateManager->decline($orderContainer);
        }

        return $orderContainer;
    }
}
