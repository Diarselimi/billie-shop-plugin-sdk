<?php

namespace App\Application\UseCase\CheckoutSessionConfirmOrder;

use App\Application\Exception\CheckoutSessionConfirmException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;

class CheckoutSessionConfirmUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $orderRepository;

    private $orderResponseFactory;

    private $orderPersistenceService;

    private $stateManager;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderResponseFactory $orderResponseFactory,
        OrderPersistenceService $orderPersistenceService,
        OrderStateManager $orderStateManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->orderPersistenceService = $orderPersistenceService;
        $this->stateManager = $orderStateManager;
    }

    /**
     * @throws OrderNotFoundException|CheckoutSessionConfirmException
     */
    public function execute(CheckoutSessionConfirmOrderRequest $request, string $checkoutSessionUuid): OrderResponse
    {
        $this->validateRequest($request);

        $orderEntity = $this->orderRepository
            ->getOneByCheckoutSessionUuidAndState($checkoutSessionUuid, OrderStateManager::STATE_AUTHORIZED);

        if (!$orderEntity) {
            throw new OrderNotFoundException();
        }

        $orderContainer = $this->orderPersistenceService->createFromOrderEntity($orderEntity);

        if (!$this->compare($request, $orderEntity)) {
            throw new CheckoutSessionConfirmException();
        }

        $this->stateManager->approve($orderContainer);

        return $this->orderResponseFactory->create($orderContainer);
    }

    private function compare(CheckoutSessionConfirmOrderRequest $confirmOrderRequest, OrderEntity $orderEntity): bool
    {
        return $confirmOrderRequest->getAmount()->getGross() === $orderEntity->getAmountGross() &&
            $confirmOrderRequest->getAmount()->getNet() === $orderEntity->getAmountNet() &&
            $confirmOrderRequest->getAmount()->getTax() === $orderEntity->getAmountTax() &&
            $confirmOrderRequest->getDuration() === $orderEntity->getDuration();
    }
}
