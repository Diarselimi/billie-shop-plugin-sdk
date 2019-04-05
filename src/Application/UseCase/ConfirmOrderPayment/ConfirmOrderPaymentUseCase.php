<?php

namespace App\Application\UseCase\ConfirmOrderPayment;

use App\Application\Exception\FraudOrderException;
use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use Symfony\Component\HttpFoundation\Response;

class ConfirmOrderPaymentUseCase
{
    private $orderRepository;

    private $paymentService;

    private $orderStateManager;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        BorschtInterface $paymentService,
        OrderStateManager $orderStateManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->paymentService = $paymentService;
        $this->orderStateManager = $orderStateManager;
    }

    public function execute(ConfirmOrderPaymentRequest $request): void
    {
        $order = $this->orderRepository->getOneByMerchantIdAndExternalCodeOrUUID($request->getOrderId(), $request->getMerchantId());

        if (!$order) {
            throw new PaellaCoreCriticalException(
                "Order #{$request->getOrderId()} not found",
                PaellaCoreCriticalException::CODE_NOT_FOUND,
                Response::HTTP_NOT_FOUND
            );
        }

        if ($order->getMarkedAsFraudAt()) {
            throw new FraudOrderException();
        }

        if ($this->orderStateManager->canConfirmPayment($order)) {
            $this->paymentService->confirmPayment($order, $request->getAmount());
        } else {
            throw new PaellaCoreCriticalException(
                "Order #{$request->getOrderId()} payment can not be confirmed",
                PaellaCoreCriticalException::CODE_ORDER_PAYMENT_CANT_BE_CONFIRMED,
                Response::HTTP_FORBIDDEN
            );
        }
    }
}
