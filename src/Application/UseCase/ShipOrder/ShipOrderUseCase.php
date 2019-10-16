<?php

namespace App\Application\UseCase\ShipOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Payment\PaymentRequestFactory;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderInvoice\InvoiceUploadHandlerInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use App\DomainModel\OrderInvoice\OrderInvoiceUploadException;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
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
        Workflow $workflow,
        OrderRepositoryInterface $orderRepository,
        PaymentsServiceInterface $paymentsService,
        OrderInvoiceManager $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        OrderResponseFactory $orderResponseFactory,
        PaymentRequestFactory $paymentRequestFactory,
        UuidGeneratorInterface $uuidGenerator,
        OrderStateManager $orderStateManager
    ) {
        $this->workflow = $workflow;
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
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalIdOrUuid(
                $request->getMerchantId(),
                $request->getOrderId()
            );
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        $order = $orderContainer->getOrder();
        $groups = is_null($order->getExternalCode()) ? ['Default', 'RequiredExternalCode'] : ['Default'];

        $this->validateRequest($request, null, $groups);

        if (!$this->workflow->can($order, OrderStateManager::TRANSITION_SHIP)) {
            throw new OrderWorkflowException("Order state does not support shipment");
        }

        $order
            ->setInvoiceNumber($request->getInvoiceNumber())
            ->setInvoiceUrl($request->getInvoiceUrl())
            ->setProofOfDeliveryUrl($request->getShippingDocumentUrl())
            ->setShippedAt(new \DateTime())
        ;

        if ($request->getExternalCode() && !$order->getExternalCode()) {
            $order->setExternalCode($request->getExternalCode());
        }

        $order->setPaymentId($this->uuidGenerator->uuid4());

        try {
            $this->paymentsService->createOrder(
                $this->paymentRequestFactory->createCreateRequestDTO($orderContainer)
            );
        } catch (PaymentsServiceRequestException $exception) {
            $this->orderRepository->update($order);

            throw new ShipOrderException("Payments call unsuccessful", null, $exception);
        }

        $this->orderStateManager->ship($orderContainer);

        try {
            $this->invoiceManager->upload($order, InvoiceUploadHandlerInterface::EVENT_SHIPMENT);
        } catch (OrderInvoiceUploadException $exception) {
            throw new ShipOrderException("Invoice can't be scheduled for upload", null, $exception);
        }

        return $this->orderResponseFactory->create($orderContainer);
    }
}
