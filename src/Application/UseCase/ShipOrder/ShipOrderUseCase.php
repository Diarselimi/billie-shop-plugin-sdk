<?php

namespace App\Application\UseCase\ShipOrder;

use App\Application\PaellaCoreCriticalException;
use App\Application\UseCase\Response\OrderResponse;
use App\Application\UseCase\Response\OrderResponseFactory;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderInvoice\InvoiceUploadHandlerInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use App\DomainModel\OrderInvoice\OrderInvoiceUploadException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Workflow\Workflow;

class ShipOrderUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $orderRepository;

    private $merchantDebtorRepository;

    private $paymentsService;

    private $workflow;

    private $invoiceManager;

    private $orderPersistenceService;

    private $orderResponseFactory;

    public function __construct(
        Workflow $workflow,
        OrderRepositoryInterface $orderRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        BorschtInterface $paymentsService,
        OrderInvoiceManager $invoiceManager,
        OrderPersistenceService $orderPersistenceService,
        OrderResponseFactory $orderResponseFactory
    ) {
        $this->workflow = $workflow;
        $this->orderRepository = $orderRepository;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->paymentsService = $paymentsService;
        $this->invoiceManager = $invoiceManager;
        $this->orderPersistenceService = $orderPersistenceService;
        $this->orderResponseFactory = $orderResponseFactory;
    }

    public function execute(ShipOrderRequest $request): OrderResponse
    {
        $order = $this->orderRepository->getOneByMerchantIdAndExternalCodeOrUUID($request->getOrderId(), $request->getMerchantId());

        if (!$order) {
            throw new PaellaCoreCriticalException(
                "Order #{$request->getOrderId()} not found",
                PaellaCoreCriticalException::CODE_NOT_FOUND,
                Response::HTTP_NOT_FOUND
            );
        }

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
            ->setProofOfDeliveryUrl($request->getProofOfDeliveryUrl())
            ->setShippedAt(new \DateTime())
        ;

        if ($request->getExternalCode() && !$order->getExternalCode()) {
            $order->setExternalCode($request->getExternalCode());
        }

        $company = $this->merchantDebtorRepository->getOneById($order->getMerchantDebtorId());
        $paymentDetails = $this->paymentsService->createOrder($order, $company->getPaymentDebtorId());
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

        $orderContainer = $this->orderPersistenceService->createFromOrderEntity($order);

        return $this->orderResponseFactory->create($orderContainer);
    }
}
