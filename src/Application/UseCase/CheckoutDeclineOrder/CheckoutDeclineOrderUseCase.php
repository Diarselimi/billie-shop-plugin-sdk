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
use Ozean12\Sepa\Client\DomainModel\SepaClientInterface;
use Ozean12\Support\HttpClient\Exception\HttpClientExceptionInterface;
use Symfony\Component\Workflow\Registry;

class CheckoutDeclineOrderUseCase implements LoggingInterface
{
    use LoggingTrait;

    private Registry $workflowRegistry;

    private DeclineOrderService $declineOrderService;

    private OrderContainerFactory $orderContainerFactory;

    private CheckoutSessionRepositoryInterface $checkoutSessionRepository;

    private DebtorExternalDataRepositoryInterface $debtorExternalDataRepository;

    private SepaClientInterface $sepaClient;

    public function __construct(
        Registry $workflowRegistry,
        DeclineOrderService $declineOrderService,
        OrderContainerFactory $orderContainerFactory,
        CheckoutSessionRepositoryInterface $checkoutSessionRepository,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        SepaClientInterface $sepaClient
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->declineOrderService = $declineOrderService;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->checkoutSessionRepository = $checkoutSessionRepository;
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
        $this->sepaClient = $sepaClient;
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

        if ($input->isWronglyIdentified()) {
            $this->debtorExternalDataRepository->invalidateMerchantExternalId($externalId);
        }

        try {
            if ($order->getDebtorSepaMandateUuid() !== null) {
                $this->sepaClient->revokeMandate($order->getDebtorSepaMandateUuid());
            }
        } catch (HttpClientExceptionInterface $exception) {
            $this->logSuppressedException(
                $exception,
                sprintf('Mandate revoke call failed for uuid %s ', $order->getDebtorSepaMandateUuid())
            );
        }

        $this->logInfo("Decline checkout order triggered.", [
            LoggingInterface::KEY_UUID => $input->getSessionUuid(),
            LoggingInterface::KEY_SOBAKA => [
                'reason' => $input->getReason(),
                'merchant_external_id' => $externalId,
                'session_uuid' => $input->getSessionUuid(),
            ],
        ]);
    }
}
