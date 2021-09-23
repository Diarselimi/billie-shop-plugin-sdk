<?php

declare(strict_types=1);

namespace App\Application\UseCase\UpdateOrder;

use App\Application\CommandHandler;
use App\Application\Exception\OrderBeingCollectedException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderUpdate\UpdateOrderAmountException;
use App\DomainModel\OrderUpdate\UpdateOrderAmountService;
use App\DomainModel\OrderUpdate\UpdateOrderException;
use Symfony\Component\Workflow\Registry;

class UpdateOrderUseCase implements ValidatedUseCaseInterface, CommandHandler
{
    use ValidatedUseCaseTrait;

    private UpdateOrderAmountService $amountService;

    private OrderContainerFactory $orderContainerFactory;

    private OrderRepositoryInterface $orderRepository;

    private Registry $workflow;

    public function __construct(
        UpdateOrderAmountService $amountService,
        OrderContainerFactory $orderContainerFactory,
        OrderRepositoryInterface $orderRepository,
        Registry $workflowRegistry
    ) {
        $this->amountService = $amountService;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->orderRepository = $orderRepository;
        $this->workflow = $workflowRegistry;
    }

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
        $order = $orderContainer->getOrder();

        if ($input->isExternalCodeChanged() && $order->getExternalCode() !== null) {
            throw new UpdateOrderException('Order already has an external_code.');
        }

        if ($input->isExternalCodeChanged()) {
            $order->setExternalCode($input->getExternalCode());
            $this->orderRepository->update($order);
        }

        if (!$input->isAmountChanged()) {
            return;
        }

        try {
            $this->amountService->update($orderContainer, $input->getAmount());
        } catch (UpdateOrderException | UpdateOrderAmountException | OrderBeingCollectedException $e) {
            throw new UpdateOrderException($e->getMessage());
        }

        if ($orderContainer->getOrderFinancialDetails()->getUnshippedAmountGross()->isZero()) {
            $this->transitOrderToTheNextState($orderContainer);
        }
    }

    private function transitOrderToTheNextState(OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();

        if ($orderContainer->getInvoices()->hasOpenInvoices()) {
            $this->workflow->get($order)->apply($order, OrderEntity::TRANSITION_SHIP_FULLY);

            return;
        }

        if (!$orderContainer->getInvoices()->hasCompletedInvoice()) {
            $this->workflow->get($order)->apply($order, OrderEntity::TRANSITION_CANCEL);

            return;
        }

        $this->workflow->get($order)->apply($order, OrderEntity::TRANSITION_COMPLETE);
    }
}
