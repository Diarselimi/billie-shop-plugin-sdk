<?php

declare(strict_types=1);

namespace App\DomainModel\ShipOrder;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderPayment\OrderPaymentService;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use App\DomainModel\Payment\PaymentsServiceRequestException;
use Symfony\Component\Workflow\Registry;

class ShipOrderService implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private Registry $workflowRegistry;

    private OrderResponseFactory $orderResponseFactory;

    private OrderRepositoryInterface $orderRepository;

    private OrderPaymentService $orderPaymentService;

    public function __construct(
        Registry $workflowRegistry,
        OrderResponseFactory $orderResponseFactory,
        OrderRepositoryInterface $orderRepository,
        OrderPaymentService $orderPaymentService
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->orderRepository = $orderRepository;
        $this->orderPaymentService = $orderPaymentService;
    }

    public function validate(AbstractShipOrderRequest $request, OrderEntity $order): void
    {
        $validationGroups = $order->getExternalCode() ? ['Default'] : ['Default', 'RequiredExternalCode'];
        $this->validateRequest($request, null, $validationGroups);

        if (!$this->workflowRegistry->get($order)->can($order, OrderEntity::TRANSITION_SHIP)) {
            throw new WorkflowException("Order state '{$order->getState()}' does not support shipment");
        }
    }

    public function hasPaymentDetails(OrderContainer $orderContainer): bool
    {
        $order = $orderContainer->getOrder();

        $paymentDetails = $this->orderPaymentService->findPaymentDetails($order);
        if ($paymentDetails) {
            $orderContainer->setPaymentDetails($paymentDetails);

            return true;
        }

        return false;
    }

    public function shipOrder(OrderContainer $orderContainer, bool $shippedInPayments): OrderResponse
    {
        $this->orderRepository->update($orderContainer->getOrder());

        if (!$shippedInPayments) {
            try {
                $this->orderPaymentService->createPaymentsTicket($orderContainer);
            } catch (PaymentsServiceRequestException $exception) {
                throw new ShipOrderException('Payments call unsuccessful', 0, $exception);
            }
        }

        $order = $orderContainer->getOrder();
        $this->workflowRegistry->get($order)->apply($order, OrderEntity::TRANSITION_SHIP);

        return $this->orderResponseFactory->create($orderContainer);
    }
}
