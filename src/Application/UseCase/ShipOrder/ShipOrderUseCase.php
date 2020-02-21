<?php

namespace App\Application\UseCase\ShipOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderInvoice\InvoiceUploadHandlerInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use App\DomainModel\OrderInvoice\OrderInvoiceUploadException;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use App\DomainModel\Payment\OrderPaymentDetailsDTO;
use App\DomainModel\Payment\PaymentRequestFactory;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Payment\PaymentsServiceRequestException;
use App\Helper\Uuid\UuidGeneratorInterface;
use Symfony\Component\Workflow\Workflow;

class ShipOrderUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $orderRepository;

    private $paymentsService;

    private $workflow;

    private $invoiceManager;

    private $orderContainerFactory;

    private $orderResponseFactory;

    private $paymentRequestFactory;

    private $uuidGenerator;

    private $orderStateManager;

    public function __construct(
        Workflow $orderWorkflow,
        OrderRepositoryInterface $orderRepository,
        PaymentsServiceInterface $paymentsService,
        OrderInvoiceManager $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        OrderResponseFactory $orderResponseFactory,
        PaymentRequestFactory $paymentRequestFactory,
        UuidGeneratorInterface $uuidGenerator,
        OrderStateManager $orderStateManager
    ) {
        $this->workflow = $orderWorkflow;
        $this->orderRepository = $orderRepository;
        $this->paymentsService = $paymentsService;
        $this->invoiceManager = $invoiceManager;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->paymentRequestFactory = $paymentRequestFactory;
        $this->uuidGenerator = $uuidGenerator;
        $this->orderStateManager = $orderStateManager;
    }

    public function execute(ShipOrderRequest $request): OrderResponse
    {
        try {
            $orderContainer = $this
                ->orderContainerFactory
                ->loadByMerchantIdAndExternalIdOrUuid($request->getMerchantId(), $request->getOrderId());
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        $order = $orderContainer->getOrder();
        $this->validate($request, $order);

        $paymentDetails = $this->findPaymentDetails($order);
        $wasShippedInPayments = $paymentDetails !== null;
        if ($paymentDetails) {
            $orderContainer->setPaymentDetails($paymentDetails);
        }

        $this->addRequestData($request, $order);

        if ($wasShippedInPayments && $this->workflow->can($order, OrderStateManager::TRANSITION_SHIP)) {
            // fix paella-side shipment
            $this->orderStateManager->ship($orderContainer);

            return $this->orderResponseFactory->create($orderContainer);
        }

        if (!$this->workflow->can($order, OrderStateManager::TRANSITION_SHIP)) {
            throw new WorkflowException("Order state '{$order->getState()}' does not support shipment");
        }

        $this->uploadInvoice($order);
        $this->createPaymentsTicket($orderContainer);

        $this->orderStateManager->ship($orderContainer);

        return $this->orderResponseFactory->create($orderContainer);
    }

    private function validate(ShipOrderRequest $request, OrderEntity $order)
    {
        $validationGroups = $order->getExternalCode() === null ? ['Default', 'RequiredExternalCode'] : ['Default'];
        $this->validateRequest($request, null, $validationGroups);
    }

    private function findPaymentDetails(OrderEntity $order): ?OrderPaymentDetailsDTO
    {
        if (!$order->getPaymentId()) {
            return null;
        }

        try {
            $paymentDetails = $this->paymentsService->getOrderPaymentDetails($order->getPaymentId());
        } catch (PaymentsServiceRequestException $exception) {
            $paymentDetails = null;
        }

        return $paymentDetails;
    }

    private function addRequestData(ShipOrderRequest $request, OrderEntity $order): void
    {
        $order
            ->setInvoiceNumber($request->getInvoiceNumber())
            ->setInvoiceUrl($request->getInvoiceUrl())
            ->setProofOfDeliveryUrl($request->getShippingDocumentUrl())
            ->setShippedAt(new \DateTime());

        if ($request->getExternalCode() && !$order->getExternalCode()) {
            $order->setExternalCode($request->getExternalCode());
        }

        if (!$order->getPaymentId()) {
            $order->setPaymentId($this->uuidGenerator->uuid4());
        }
    }

    private function uploadInvoice(OrderEntity $order): void
    {
        try {
            $this->invoiceManager->upload($order, InvoiceUploadHandlerInterface::EVENT_SHIPMENT);
        } catch (OrderInvoiceUploadException $exception) {
            throw new ShipOrderException("Invoice can't be scheduled for upload", null, $exception);
        }
    }

    private function createPaymentsTicket(OrderContainer $orderContainer): void
    {
        try {
            $paymentRequest = $this->paymentRequestFactory->createCreateRequestDTO($orderContainer);
            $paymentDetails = $this->paymentsService->createOrder($paymentRequest);

            $orderContainer->setPaymentDetails($paymentDetails);
        } catch (PaymentsServiceRequestException $exception) {
            $this->orderRepository->update($orderContainer->getOrder());

            throw new ShipOrderException('Payments call unsuccessful', null, $exception);
        }
    }
}
