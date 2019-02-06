<?php

namespace App\Application\UseCase\ShipOrder;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Workflow\Workflow;

class ShipOrderUseCase
{
    private $orderRepository;

    private $merchantDebtorRepository;

    private $paymentsService;

    private $workflow;

    public function __construct(
        Workflow $workflow,
        OrderRepositoryInterface $orderRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        BorschtInterface $paymentsService
    ) {
        $this->workflow = $workflow;
        $this->orderRepository = $orderRepository;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->paymentsService = $paymentsService;
    }

    public function execute(ShipOrderRequest $request): void
    {
        $externalCode = $request->getExternalCode();
        $customerId = $request->getCustomerId();
        $order = $this->orderRepository->getOneByExternalCode($externalCode, $customerId);

        if (!$order) {
            throw new PaellaCoreCriticalException(
                "Order #$externalCode not found",
                PaellaCoreCriticalException::CODE_NOT_FOUND,
                Response::HTTP_NOT_FOUND
            );
        }
        if (!$this->workflow->can($order, OrderStateManager::TRANSITION_SHIP)) {
            throw new PaellaCoreCriticalException(
                "Order #$externalCode can not be shipped",
                PaellaCoreCriticalException::CODE_ORDER_CANT_BE_SHIPPED
            );
        }

        $order
            ->setInvoiceNumber($request->getInvoiceNumber())
            ->setInvoiceUrl($request->getInvoiceUrl())
            ->setProofOfDeliveryUrl($request->getProofOfDeliveryUrl())
            ->setShippedAt(new \DateTime())
        ;

        $company = $this->merchantDebtorRepository->getOneById($order->getMerchantDebtorId());
        $paymentDetails = $this->paymentsService->createOrder($order, $company->getPaymentDebtorId());
        $order->setPaymentId($paymentDetails->getId());

        $this->workflow->apply($order, OrderStateManager::TRANSITION_SHIP);
        $this->orderRepository->update($order);
    }
}
