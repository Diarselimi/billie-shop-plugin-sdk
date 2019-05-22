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
use Symfony\Component\Workflow\Workflow;

class CheckoutSessionConfirmUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $orderRepository;

    private $orderResponseFactory;

    private $orderPersistenceService;

    private $workflow;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderResponseFactory $orderResponseFactory,
        OrderPersistenceService $orderPersistenceService,
        Workflow $workflow
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->orderPersistenceService = $orderPersistenceService;
        $this->workflow = $workflow;
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

        #TODO: This should be removed and moved to the new created service
        $this->workflow->apply($orderContainer->getOrder(), OrderStateManager::TRANSITION_CREATE);
        $this->orderRepository->update($orderContainer->getOrder());

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
