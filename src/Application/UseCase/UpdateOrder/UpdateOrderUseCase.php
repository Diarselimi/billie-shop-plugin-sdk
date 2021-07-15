<?php

declare(strict_types=1);

namespace App\Application\UseCase\UpdateOrder;

use App\Application\Exception\OrderBeingCollectedException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderUpdate\UpdateOrderAmountException;
use App\DomainModel\OrderUpdate\UpdateOrderAmountService;
use App\DomainModel\OrderUpdate\UpdateOrderException;

class UpdateOrderUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private UpdateOrderAmountService $amountService;

    private OrderContainerFactory $orderContainerFactory;

    private OrderRepositoryInterface $orderRepository;

    public function __construct(
        UpdateOrderAmountService $amountService,
        OrderContainerFactory $orderContainerFactory,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->amountService = $amountService;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @throws OrderNotFoundException
     */
    public function execute(UpdateOrderRequest $input): void
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalIdOrUuid(
                $input->getMerchantId(),
                $input->getOrderUuid()
            );
        } catch (OrderContainerFactoryException $e) {
            throw new OrderNotFoundException();
        }

        $this->validateRequest($input);

        if ($input->isAmountChanged()) {
            try {
                $this->amountService->update($orderContainer, $input->getAmount());
            } catch (UpdateOrderException | UpdateOrderAmountException | OrderBeingCollectedException $e) {
                throw new UpdateOrderException($e->getMessage());
            }
        }

        $order = $orderContainer->getOrder();
        if ($input->isExternalCodeChanged() && $order->getExternalCode() !== null) {
            throw new UpdateOrderException('Order already has an external_code.');
        }

        if ($input->isExternalCodeChanged()) {
            $order->setExternalCode($input->getExternalCode());
            $this->orderRepository->update($order);
        }
    }
}
