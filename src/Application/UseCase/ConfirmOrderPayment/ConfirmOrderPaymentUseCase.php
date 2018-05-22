<?php

namespace App\Application\UseCase\ConfirmOrderPayment;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use Symfony\Component\HttpFoundation\Response;

class ConfirmOrderPaymentUseCase
{
    private $orderRepository;
    private $borscht;
    private $orderStateManager;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        AlfredInterface $alfred,
        BorschtInterface $borscht,
        OrderStateManager $orderStateManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->borscht = $borscht;
        $this->orderStateManager = $orderStateManager;
    }

    public function execute(ConfirmOrderPaymentRequest $request): void
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

        $amount = $request->getAmount();
        if ($this->orderStateManager->canConfirmPayment($order)) {
            $this->borscht->confirmPayment($order, $amount);
        } else {
            throw new PaellaCoreCriticalException(
                "Order #$externalCode payment can not be confirmed",
                PaellaCoreCriticalException::CODE_ORDER_PAYMENT_CANT_BE_CONFIRMED,
                Response::HTTP_FORBIDDEN
            );
        }
    }
}
