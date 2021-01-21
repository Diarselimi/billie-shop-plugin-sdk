<?php

namespace App\DomainModel\Order\Lifecycle\ShipOrder;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderPayment\OrderPaymentService;
use App\DomainModel\Payment\PaymentsServiceRequestException;
use App\DomainModel\ShipOrder\ShipOrderException;
use App\Helper\Uuid\UuidGeneratorInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Registry;

class LegacyShipOrderService implements ShipOrderInterface, LoggingInterface
{
    use LoggingTrait;

    private OrderPaymentService $orderPaymentService;

    private Registry $workflowRegistry;

    private UuidGeneratorInterface $uuidGenerator;

    private OrderRepositoryInterface $orderRepository;

    public function __construct(
        OrderPaymentService $orderPaymentService,
        Registry $workflowRegistry,
        UuidGeneratorInterface $uuidGenerator,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderPaymentService = $orderPaymentService;
        $this->workflowRegistry = $workflowRegistry;
        $this->uuidGenerator = $uuidGenerator;
        $this->orderRepository = $orderRepository;
    }

    public function ship(OrderContainer $orderContainer, Invoice $invoice): void
    {
        $order = $orderContainer->getOrder();
        $workflow = $this->workflowRegistry->get($order);

        if ($order->getPaymentId() === null) {
            $order->setPaymentId($this->uuidGenerator->uuid4())
                ->setShippedAt(new \DateTime())
            ;
            $this->orderRepository->update($order);
        }

        if (!$this->hasPaymentDetails($orderContainer)) {
            try {
                $this->orderPaymentService->createPaymentsTicket($orderContainer);
            } catch (PaymentsServiceRequestException $exception) {
                throw new ShipOrderException('Payments call unsuccessful', 0, $exception);
            }
        }

        if ($order->isWorkflowV1()) {
            $workflow->apply($order, OrderEntity::TRANSITION_SHIP);
        }

        $this->logInfo('Order shipped with {name} workflow', [LoggingInterface::KEY_NAME => $workflow->getName()]);
    }

    private function hasPaymentDetails(OrderContainer $orderContainer): bool
    {
        $paymentDetails = $this->orderPaymentService->findPaymentDetails($orderContainer->getOrder());
        if ($paymentDetails) {
            $orderContainer->setPaymentDetails($paymentDetails);

            return true;
        }

        return false;
    }
}
