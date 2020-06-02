<?php

declare(strict_types=1);

namespace App\DomainModel\ShipOrder;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderPayment\OrderPaymentService;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use App\DomainModel\Payment\PaymentsServiceRequestException;
use App\Helper\Uuid\UuidGeneratorInterface;

class ShipOrderService implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $uuidGenerator;

    private $orderStateManager;

    private $orderResponseFactory;

    private $orderRepository;

    private $orderPaymentService;

    public function __construct(
        UuidGeneratorInterface $uuidGenerator,
        OrderStateManager $orderStateManager,
        OrderResponseFactory $orderResponseFactory,
        OrderRepositoryInterface $orderRepository,
        OrderPaymentService $orderPaymentService
    ) {
        $this->uuidGenerator = $uuidGenerator;
        $this->orderStateManager = $orderStateManager;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->orderRepository = $orderRepository;
        $this->orderPaymentService = $orderPaymentService;
    }

    public function validate(AbstractShipOrderRequest $request, OrderEntity $order)
    {
        $validationGroups = $order->getExternalCode() === null ? ['Default', 'RequiredExternalCode'] : ['Default'];
        $this->validateRequest($request, null, $validationGroups);

        if (!$this->orderStateManager->can($order, OrderStateManager::TRANSITION_SHIP)) {
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
        } else {
            return false;
        }
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

        $this->orderStateManager->ship($orderContainer);

        return $this->orderResponseFactory->create($orderContainer);
    }
}
