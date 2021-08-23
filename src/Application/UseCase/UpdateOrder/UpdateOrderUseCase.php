<?php

declare(strict_types=1);

namespace App\Application\UseCase\UpdateOrder;

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
use App\DomainModel\OrderUpdate\UpdateOrderAmountValidator;
use App\DomainModel\OrderUpdate\UpdateOrderException;
use Symfony\Component\Workflow\Registry;

class UpdateOrderUseCase implements ValidatedUseCaseInterface
{
    private const ORDER_UPDATE_ALLOWED_STATES = [
        OrderEntity::STATE_SHIPPED,
        OrderEntity::STATE_PAID_OUT,
        OrderEntity::STATE_LATE,
        OrderEntity::STATE_WAITING,
        OrderEntity::STATE_CREATED,
        OrderEntity::STATE_PARTIALLY_SHIPPED,
    ];

    use ValidatedUseCaseTrait;

    private UpdateOrderAmountService $amountService;

    private OrderContainerFactory $orderContainerFactory;

    private OrderRepositoryInterface $orderRepository;

    private Registry $workflow;

    private UpdateOrderAmountValidator $amountValidator;

    public function __construct(
        UpdateOrderAmountValidator $amountValidator,
        UpdateOrderAmountService $amountService,
        OrderContainerFactory $orderContainerFactory,
        OrderRepositoryInterface $orderRepository,
        Registry $workflowRegistry
    ) {
        $this->amountService = $amountService;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->orderRepository = $orderRepository;
        $this->workflow = $workflowRegistry;
        $this->amountValidator = $amountValidator;
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

        if (!in_array($orderContainer->getOrder()->getState(), self::ORDER_UPDATE_ALLOWED_STATES)) {
            throw new UpdateOrderException(
                sprintf(
                    'Order cannot be updated only when it\'s in (%s) states.',
                    implode(',', self::ORDER_UPDATE_ALLOWED_STATES)
                )
            );
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

        if ($orderContainer->getInvoices()->isEmpty() && $orderContainer->getOrderFinancialDetails()->getUnshippedAmountGross()->isZero()) {
            throw new UpdateOrderException('Order cannot be updated, try to cancel it instead.');
        }

        if (!$orderContainer->getOrderFinancialDetails()->getUnshippedAmountGross()->isZero()) {
            return;
        }

        $this->transitionOrderToTheRightState($orderContainer, $order);
    }

    private function transitionOrderToTheRightState(
        OrderContainer $orderContainer,
        OrderEntity $order
    ): void {
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
