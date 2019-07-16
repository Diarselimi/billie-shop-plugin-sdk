<?php

namespace App\Application\UseCase\ConfirmOrderPayment;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\PaymentOrderConfirmException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;

class ConfirmOrderPaymentUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $orderRepository;

    private $paymentService;

    private $orderStateManager;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        PaymentsServiceInterface $paymentService,
        OrderStateManager $orderStateManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->paymentService = $paymentService;
        $this->orderStateManager = $orderStateManager;
    }

    /**
     * @param  ConfirmOrderPaymentRequest   $request
     * @throws FraudOrderException
     * @throws OrderNotFoundException
     * @throws PaymentOrderConfirmException
     */
    public function execute(ConfirmOrderPaymentRequest $request): void
    {
        $this->validateRequest($request);

        $order = $this->orderRepository->getOneByMerchantIdAndExternalCodeOrUUID($request->getOrderId(), $request->getMerchantId());

        if (!$order) {
            throw new OrderNotFoundException();
        }

        if ($order->getMarkedAsFraudAt()) {
            throw new FraudOrderException();
        }

        if ($this->orderStateManager->wasShipped($order)) {
            $this->paymentService->confirmPayment($order, $request->getAmount());
        } else {
            throw new PaymentOrderConfirmException();
        }
    }
}
