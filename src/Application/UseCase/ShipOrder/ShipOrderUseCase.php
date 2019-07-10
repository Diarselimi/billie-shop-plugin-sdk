<?php

namespace App\Application\UseCase\ShipOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\PaellaCoreCriticalException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderInvoice\InvoiceUploadHandlerInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use App\DomainModel\OrderInvoice\OrderInvoiceUploadException;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;
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

    public function __construct(
        Workflow $workflow,
        OrderRepositoryInterface $orderRepository,
        BorschtInterface $paymentsService,
        OrderInvoiceManager $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        OrderResponseFactory $orderResponseFactory
    ) {
        $this->workflow = $workflow;
        $this->orderRepository = $orderRepository;
        $this->paymentsService = $paymentsService;
        $this->invoiceManager = $invoiceManager;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->orderResponseFactory = $orderResponseFactory;
    }

    public function execute(ShipOrderRequest $request): OrderResponse
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalId(
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
            throw new PaellaCoreCriticalException(
                "Order #{$request->getOrderId()} can not be shipped",
                PaellaCoreCriticalException::CODE_ORDER_CANT_BE_SHIPPED
            );
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

        $paymentDetails = $this->paymentsService->createOrder(
            $orderContainer->getMerchantDebtor()->getPaymentDebtorId(),
            $order->getInvoiceNumber(),
            $order->getShippedAt(),
            $orderContainer->getOrderFinancialDetails()->getDuration(),
            $orderContainer->getOrderFinancialDetails()->getAmountGross(),
            $order->getExternalCode()
        );
        $order->setPaymentId($paymentDetails->getId());

        $this->workflow->apply($order, OrderStateManager::TRANSITION_SHIP);
        $this->orderRepository->update($order);

        try {
            $this->invoiceManager->upload($order, InvoiceUploadHandlerInterface::EVENT_SHIPMENT);
        } catch (OrderInvoiceUploadException $exception) {
            throw new PaellaCoreCriticalException(
                "Order #{$request->getOrderId()} can not be shipped",
                PaellaCoreCriticalException::CODE_ORDER_CANT_BE_SHIPPED,
                500,
                $exception
            );
        }

        return $this->orderResponseFactory->create($orderContainer);
    }
}
