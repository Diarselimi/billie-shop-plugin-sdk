<?php

namespace App\Application\UseCase\CancelOrder;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsException;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderStateManager;
use Symfony\Component\Workflow\Workflow;

class CancelOrderUseCase
{
    private $limitsService;

    private $paymentsService;

    private $workflow;

    private $orderContainerFactory;

    private $merchantRepository;

    private $orderStateManager;

    public function __construct(
        Workflow $orderWorkflow,
        MerchantDebtorLimitsService $limitsService,
        PaymentsServiceInterface $paymentsService,
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        OrderStateManager $orderStateManager
    ) {
        $this->workflow = $orderWorkflow;
        $this->limitsService = $limitsService;
        $this->paymentsService = $paymentsService;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->merchantRepository = $merchantRepository;
        $this->orderStateManager = $orderStateManager;
    }

    public function execute(CancelOrderRequest $request): void
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

        if ($order->getMarkedAsFraudAt()) {
            throw new FraudOrderException();
        }

        if ($this->workflow->can($order, OrderStateManager::TRANSITION_CANCEL)) {
            $orderContainer->getMerchant()->increaseFinancingLimit($orderContainer->getOrderFinancialDetails()->getAmountGross());
            $this->merchantRepository->update($orderContainer->getMerchant());

            try {
                $this->limitsService->unlock($orderContainer);
            } catch (MerchantDebtorLimitsException $exception) {
                throw new LimitUnlockException("Limits cannot be unlocked for merchant #{$orderContainer->getMerchantDebtor()->getId()}");
            }

            $this->orderStateManager->cancel($orderContainer);
        } elseif ($this->workflow->can($order, OrderStateManager::TRANSITION_CANCEL_SHIPPED)) {
            $this->paymentsService->cancelOrder($order);

            $this->orderStateManager->cancelShipped($orderContainer);
        } else {
            throw new CancelOrderException("Order #{$request->getOrderId()} can not be cancelled");
        }
    }
}
