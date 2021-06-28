<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutDeclineOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Order\Lifecycle\DeclineOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Registry;

class CheckoutDeclineOrderUseCase implements LoggingInterface
{
    use LoggingTrait;

    private Registry $workflowRegistry;

    private DeclineOrderService $declineOrderService;

    private OrderContainerFactory $orderContainerFactory;

    private CheckoutSessionRepositoryInterface $checkoutSessionRepository;

    private DebtorExternalDataRepositoryInterface $debtorExternalDataRepository;

    public function __construct(
        Registry $workflowRegistry,
        DeclineOrderService $declineOrderService,
        OrderContainerFactory $orderContainerFactory,
        CheckoutSessionRepositoryInterface $checkoutSessionRepository,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->declineOrderService = $declineOrderService;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->checkoutSessionRepository = $checkoutSessionRepository;
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
    }

    /**
     * @throws OrderNotFoundException
     */
    public function execute(CheckoutDeclineOrderRequest $input): void
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadNotYetConfirmedByCheckoutSessionUuid($input->getSessionUuid());
        } catch (OrderContainerFactoryException $e) {
            throw new OrderNotFoundException($e);
        }

        $order = $orderContainer->getOrder();
        if (!$this->workflowRegistry->get($order)->can($order, OrderEntity::TRANSITION_DECLINE)) {
            throw new  WorkflowException('Order cannot be declined.');
        }

        $externalId = $orderContainer->getDebtorExternalData()->getMerchantExternalId();
        $this->declineOrderService->decline($orderContainer);
        $this->checkoutSessionRepository->reActivateSession($input->getSessionUuid());
        $this->debtorExternalDataRepository->invalidateMerchantExternalId($externalId);

        $this->logInfo("It's not us action triggered", [
            LoggingInterface::KEY_UUID => $input->getSessionUuid(),
            LoggingInterface::KEY_SOBAKA => [
                'merchant_external_id' => $externalId,
                'session_uuid' => $input->getSessionUuid(),
            ],
        ]);
    }
}
