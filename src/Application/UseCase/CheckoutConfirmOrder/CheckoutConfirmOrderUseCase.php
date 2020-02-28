<?php

namespace App\Application\UseCase\CheckoutConfirmOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\CheckoutSession\CheckoutOrderMatcherInterface;
use App\DomainModel\CheckoutSession\CheckoutOrderRequestDTO;
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

    private $dataMatcher;

    public function __construct(
        OrderResponseFactory $orderResponseFactory,
        OrderContainerFactory $orderContainerFactory,
        OrderStateManager $orderStateManager,
        CheckoutOrderMatcherInterface $dataMatcher
    ) {
        $this->orderResponseFactory = $orderResponseFactory;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->stateManager = $orderStateManager;
        $this->dataMatcher = $dataMatcher;
    }

    public function execute(CheckoutConfirmOrderRequest $request): OrderResponse
    {
        $this->validateRequest($request);

        try {
            $orderContainer = $this->orderContainerFactory->loadNotYetConfirmedByCheckoutSessionUuid(
                $request->getSessionUuid()
            );
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        if (!$this->hasMatchingData($request, $orderContainer)) {
            throw new CheckoutConfirmDataMismatchException();
        }

        if ($this->stateManager->isPreWaiting($orderContainer->getOrder())) {
            $this->stateManager->wait($orderContainer);
        } else {
            $this->stateManager->approve($orderContainer);
        }

        return $this->orderResponseFactory->create($orderContainer);
    }

    private function hasMatchingData(CheckoutConfirmOrderRequest $request, OrderContainer $orderContainer): bool
    {
        $orderRequestDto = (new CheckoutOrderRequestDTO())
            ->setSessionUuid($request->getSessionUuid())
            ->setAmount($request->getAmount())
            ->setDebtorCompany($request->getDebtorCompanyRequest())
            ->setDuration($request->getDuration());

        return $this->dataMatcher->matches($orderRequestDto, $orderContainer);
    }
}
