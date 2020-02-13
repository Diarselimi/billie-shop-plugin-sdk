<?php

namespace App\Application\UseCase\CheckoutConfirmOrder;

use App\Application\Exception\CheckoutSessionConfirmException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;

class CheckoutConfirmOrderUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $orderResponseFactory;

    private $orderContainerFactory;

    private $stateManager;

    public function __construct(
        OrderResponseFactory $orderResponseFactory,
        OrderContainerFactory $orderContainerFactory,
        OrderStateManager $orderStateManager
    ) {
        $this->orderResponseFactory = $orderResponseFactory;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->stateManager = $orderStateManager;
    }

    public function execute(CheckoutConfirmOrderRequest $request): OrderResponse
    {
        $this->validateRequest($request);

        try {
            $orderContainer = $this->orderContainerFactory->loadNotYetConfirmedByCheckoutSessionUuid($request->getSessionUuid());
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        if (!$this->hasMatchingData($request, $orderContainer)) {
            throw new CheckoutSessionConfirmException();
        }

        if ($this->stateManager->isPreWaiting($orderContainer->getOrder())) {
            $this->stateManager->wait($orderContainer);
        } else {
            $this->stateManager->approve($orderContainer);
        }

        return $this->orderResponseFactory->create($orderContainer);
    }

    private function hasMatchingData(CheckoutConfirmOrderRequest $confirmOrderRequest, OrderContainer $orderContainer): bool
    {
        $orderFinancialDetails = $orderContainer->getOrderFinancialDetails();

        return $confirmOrderRequest->getAmount()->getGross() === $orderFinancialDetails->getAmountGross() &&
            $confirmOrderRequest->getAmount()->getNet() === $orderFinancialDetails->getAmountNet() &&
            $confirmOrderRequest->getAmount()->getTax() === $orderFinancialDetails->getAmountTax() &&
            $confirmOrderRequest->getDuration() === $orderFinancialDetails->getDuration();
    }
}
