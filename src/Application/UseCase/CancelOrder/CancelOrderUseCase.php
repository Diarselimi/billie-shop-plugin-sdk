<?php

namespace App\Application\UseCase\CancelOrder;

use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsException;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Registry;

class CancelOrderUseCase implements LoggingInterface
{
    use LoggingTrait;

    private MerchantDebtorLimitsService $limitsService;

    private PaymentsServiceInterface $paymentsService;

    private OrderContainerFactory $orderContainerFactory;

    private MerchantRepositoryInterface $merchantRepository;

    private Registry $workflowRegistry;

    public function __construct(
        MerchantDebtorLimitsService $limitsService,
        PaymentsServiceInterface $paymentsService,
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        Registry $workflowRegistry
    ) {
        $this->limitsService = $limitsService;
        $this->paymentsService = $paymentsService;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->merchantRepository = $merchantRepository;
        $this->workflowRegistry = $workflowRegistry;
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
        $workflow = $this->workflowRegistry->get($order);

        if ($workflow->can($order, OrderEntity::TRANSITION_CANCEL)) {
            $this->logInfo('Cancel order {id}', [LoggingInterface::KEY_ID => $order->getId()]);

            $orderContainer->getMerchant()->increaseFinancingLimit(
                $orderContainer->getOrderFinancialDetails()->getAmountGross()
            );
            $this->merchantRepository->update($orderContainer->getMerchant());

            try {
                $this->limitsService->unlock($orderContainer);
            } catch (MerchantDebtorLimitsException $exception) {
                throw new LimitUnlockException("Limits cannot be unlocked for merchant #{$orderContainer->getMerchantDebtor()->getId()}");
            }

            $workflow->apply($order, OrderEntity::TRANSITION_CANCEL);
        } elseif ($workflow->can($order, OrderEntity::TRANSITION_CANCEL_SHIPPED)) {
            $this->logInfo('Cancel shipped order {id}', [LoggingInterface::KEY_ID => $order->getId()]);

            $this->paymentsService->cancelOrder($order);
            $workflow->apply($order, OrderEntity::TRANSITION_CANCEL_SHIPPED);
        } elseif ($workflow->can($order, OrderEntity::TRANSITION_CANCEL_WAITING)) {
            $this->logInfo('Cancel waiting order {id}', [LoggingInterface::KEY_ID => $order->getId()]);

            $workflow->apply($order, OrderEntity::TRANSITION_CANCEL_WAITING);
        } else {
            throw new CancelOrderException("Order #{$request->getOrderId()} can not be cancelled");
        }
    }
}
