<?php

namespace App\Application\UseCase\CancelOrder;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsException;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Workflow\Workflow;

class CancelOrderUseCase
{
    private $orderRepository;

    private $limitsService;

    private $paymentsService;

    private $workflow;

    private $orderContainerFactory;

    private $merchantRepository;

    public function __construct(
        Workflow $workflow,
        OrderRepositoryInterface $orderRepository,
        MerchantDebtorLimitsService $limitsService,
        PaymentsServiceInterface $paymentsService,
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository
    ) {
        $this->workflow = $workflow;
        $this->orderRepository = $orderRepository;
        $this->limitsService = $limitsService;
        $this->paymentsService = $paymentsService;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->merchantRepository = $merchantRepository;
    }

    public function execute(CancelOrderRequest $request): void
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
        if ($order->getMarkedAsFraudAt()) {
            throw new FraudOrderException();
        }

        if ($this->workflow->can($order, OrderStateManager::TRANSITION_CANCEL)) {
            $orderContainer->getMerchant()->increaseAvailableFinancingLimit($orderContainer->getOrderFinancialDetails()->getAmountGross());
            $this->merchantRepository->update($orderContainer->getMerchant());

            try {
                $this->limitsService->unlock($orderContainer);
            } catch (MerchantDebtorLimitsException $exception) {
                throw new PaellaCoreCriticalException(
                    "Merchant debtor limits can't be unlocked",
                    PaellaCoreCriticalException::CODE_ORDER_CANT_BE_CANCELLED,
                    Response::HTTP_BAD_REQUEST
                );
            }

            $this->workflow->apply($order, OrderStateManager::TRANSITION_CANCEL);
        } elseif ($this->workflow->can($order, OrderStateManager::TRANSITION_CANCEL_SHIPPED)) {
            $this->paymentsService->cancelOrder($order);
            $this->workflow->apply($order, OrderStateManager::TRANSITION_CANCEL_SHIPPED);
        } else {
            throw new PaellaCoreCriticalException(
                "Order #{$request->getOrderId()} can not be cancelled",
                PaellaCoreCriticalException::CODE_ORDER_CANT_BE_CANCELLED,
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->orderRepository->update($order);
    }
}
