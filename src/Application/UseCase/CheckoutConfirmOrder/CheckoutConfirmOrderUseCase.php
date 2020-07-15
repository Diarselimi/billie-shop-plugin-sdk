<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutConfirmOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\RequestValidationException;
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

        $this->assureDataMatches($request, $orderContainer);

        if ($this->stateManager->isPreWaiting($orderContainer->getOrder())) {
            $this->stateManager->wait($orderContainer);
        } else {
            $this->stateManager->approve($orderContainer);
        }

        return $this->orderResponseFactory->create($orderContainer);
    }

    private function assureDataMatches(CheckoutConfirmOrderRequest $request, OrderContainer $orderContainer): void
    {
        $orderRequestDto = (new CheckoutOrderRequestDTO())
            ->setSessionUuid($request->getSessionUuid())
            ->setAmount($request->getAmount())
            ->setDebtorCompany($request->getDebtorCompanyRequest())
            ->setDeliveryAddress($request->getDeliveryAddress())
            ->setDuration($request->getDuration());

        $mismatchViolationList = $this->dataMatcher->matches($orderRequestDto, $orderContainer);

        if ($mismatchViolationList->hasMismatches()) {
            throw new RequestValidationException($mismatchViolationList);
        }
    }
}
