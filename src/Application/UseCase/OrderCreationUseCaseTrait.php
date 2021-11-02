<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\UseCase\CreateOrder\CreateOrderRequestInterface;
use App\DomainModel\Order\CompanyIdentifier;
use App\DomainModel\Order\Lifecycle\DeclineOrderService;
use App\DomainModel\Order\NewOrder\OrderPersistenceService;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderRepository;
use App\DomainModel\OrderResponse\LegacyOrderResponseFactory;

/**
 * @deprecated we need to remove this trait
 */
trait OrderCreationUseCaseTrait
{
    private OrderPersistenceService $orderPersistenceService;

    private OrderContainerFactory $orderContainerFactory;

    private OrderChecksRunnerService $orderChecksRunnerService;

    private OrderRepository $orderRepository;

    private DeclineOrderService $declineOrderService;

    private CompanyIdentifier $companyIdentifier;

    private LegacyOrderResponseFactory $orderResponseFactory;

    private function createIdentifiedOrder(CreateOrderRequestInterface $request): OrderContainer
    {
        $orderCreationDTO = $this->orderPersistenceService->persistFromRequest($request);
        $orderContainer = $this->orderContainerFactory->createFromNewOrderDTO($orderCreationDTO);

        if (!$this->orderChecksRunnerService->passesPreIdentificationChecks($orderContainer)) {
            $this->declineOrderService->decline($orderContainer);

            return $orderContainer;
        }

        //TODO, should we move this to a handler which gets triggered by a Domain Event : OrderStarted?
        if ($this->companyIdentifier->identify($orderContainer)) {
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
