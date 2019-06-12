<?php

namespace App\Application\UseCase\CheckoutSessionConfirmOrder;

use App\Application\Exception\CheckoutSessionConfirmException;
use App\Application\Exception\OrderNotAuthorizedException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;

class CheckoutSessionConfirmUseCase implements ValidatedUseCaseInterface
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

    /**
     * @throws OrderNotAuthorizedException|CheckoutSessionConfirmException
     * @throws OrderNotFoundException
     */
    public function execute(CheckoutSessionConfirmOrderRequest $request): OrderResponse
    {
        $this->validateRequest($request);

        try {
            $orderContainer = $this->orderContainerFactory->loadByCheckoutSessionUuid($request->getSessionUuid());
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        if ($orderContainer->getOrder()->getState() !== OrderStateManager::STATE_AUTHORIZED) {
            throw new OrderNotAuthorizedException();
        }

        if (!$this->compare($request, $orderContainer)) {
            throw new CheckoutSessionConfirmException();
        }

        $this->stateManager->approve($orderContainer);

        return $this->orderResponseFactory->create($orderContainer);
    }

    private function compare(CheckoutSessionConfirmOrderRequest $confirmOrderRequest, OrderContainer $orderContainer): bool
    {
        $orderFinancialDetails = $orderContainer->getOrderFinancialDetails();

        return $confirmOrderRequest->getAmount()->getGross() === $orderFinancialDetails->getAmountGross() &&
            $confirmOrderRequest->getAmount()->getNet() === $orderFinancialDetails->getAmountNet() &&
            $confirmOrderRequest->getAmount()->getTax() === $orderFinancialDetails->getAmountTax() &&
            $confirmOrderRequest->getDuration() === $orderFinancialDetails->getDuration();
    }
}
