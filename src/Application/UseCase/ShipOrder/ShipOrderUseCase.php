<?php

namespace App\Application\UseCase\ShipOrder;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Alfred\AlfredInterface;
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
    private $alfred;
    private $borscht;
    private $workflow;

    public function __construct(
        Workflow $workflow,
        OrderRepositoryInterface $orderRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        AlfredInterface $alfred,
        BorschtInterface $borscht
    ) {
        $this->workflow = $workflow;
        $this->orderRepository = $orderRepository;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->alfred = $alfred;
        $this->borscht = $borscht;
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
            ->setShippedAt(new \DateTime())
        ;

        $company = $this->merchantDebtorRepository->getOneById($order->getMerchantDebtorId());
        $debtor = $this->alfred->getDebtor($company->getDebtorId());

        $paymentDetails = $this->borscht->createOrder($order, $debtor->getPaymentId());
        $order->setPaymentId($paymentDetails->getId());

        $this->workflow->apply($order, OrderStateManager::TRANSITION_SHIP);
        $this->orderRepository->update($order);
    }
}
