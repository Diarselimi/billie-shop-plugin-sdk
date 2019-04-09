<?php

namespace App\Application\UseCase\CreateOrder;

use App\Application\UseCase\Response\OrderResponse;
use App\Application\UseCase\Response\OrderResponseFactory;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainEvent\Order\OrderApprovedEvent;
use App\DomainEvent\Order\OrderInWaitingStateEvent;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantDebtor\DebtorFinder;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\Order\LimitsService;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\Workflow\Workflow;

class CreateOrderUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $orderPersistenceService;

    private $orderChecksRunnerService;

    private $orderRepository;

    private $workflow;

    private $limitsService;

    private $debtorFinderService;

    private $validator;

    private $producer;

    private $eventDispatcher;

    private $orderResponseFactory;

    public function __construct(
        OrderPersistenceService $orderPersistenceService,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderRepositoryInterface $orderRepository,
        Workflow $workflow,
        LimitsService $limitsService,
        DebtorFinder $debtorFinderService,
        ValidatorInterface $validator,
        ProducerInterface $producer,
        EventDispatcherInterface $eventDispatcher,
        OrderResponseFactory $orderResponseFactory
    ) {
        $this->orderPersistenceService = $orderPersistenceService;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->orderRepository = $orderRepository;
        $this->workflow = $workflow;
        $this->limitsService = $limitsService;
        $this->debtorFinderService = $debtorFinderService;
        $this->validator = $validator;
        $this->producer = $producer;
        $this->eventDispatcher = $eventDispatcher;
        $this->orderResponseFactory = $orderResponseFactory;
    }

    public function execute(CreateOrderRequest $request): OrderResponse
    {
        $this->validateRequest($request);

        $orderContainer = $this->orderPersistenceService->persistFromRequest($request);

        if (!$this->orderChecksRunnerService->runPreIdentificationChecks($orderContainer)) {
            $this->reject($orderContainer, 'preconditions checks failed');

            return $this->orderResponseFactory->create($orderContainer);
        }

        if ($this->identifyDebtor($orderContainer, $request->getMerchantId()) !== null) {
            $this->orderRepository->update($orderContainer->getOrder());
        }

        if (!$this->orderChecksRunnerService->runPostIdentificationChecks($orderContainer)) {
            $this->reject($orderContainer, 'checks failed');

            return $this->orderResponseFactory->create($orderContainer);
        }

        if ($this->orderChecksRunnerService->checkForFailedSoftDeclinableCheckResults($orderContainer)) {
            $this->moveToWaiting($orderContainer);

            return $this->orderResponseFactory->create($orderContainer);
        }

        $this->approve($orderContainer);

        return $this->orderResponseFactory->create($orderContainer);
    }

    private function identifyDebtor(OrderContainer $orderContainer, int $merchantId): ? MerchantDebtorEntity
    {
        $merchantDebtor = $this->debtorFinderService->findDebtor($orderContainer, $merchantId);

        if ($orderContainer->getMerchantSettings()->getDebtorIdentificationAlgorithm() ===
            CompaniesServiceInterface::DEBTOR_IDENTIFICATION_ALGORITHM_V1
        ) {
            $this->triggerV2DebtorIdentificationAsync(
                $orderContainer->getOrder(),
                $merchantDebtor ? $merchantDebtor->getDebtorCompany() : null
            );
        }

        if ($merchantDebtor === null) {
            return null;
        }

        $orderContainer
            ->setMerchantDebtor($merchantDebtor)
            ->getOrder()->setMerchantDebtorId($merchantDebtor->getId())
        ;

        return $merchantDebtor;
    }

    private function moveToWaiting(OrderContainer $orderContainer)
    {
        $this->workflow->apply($orderContainer->getOrder(), OrderStateManager::TRANSITION_WAITING);
        $this->orderRepository->update($orderContainer->getOrder());

        $this->eventDispatcher->dispatch(
            OrderInWaitingStateEvent::NAME,
            new OrderInWaitingStateEvent($orderContainer->getOrder())
        );

        $this->logInfo("Order was moved to waiting state");
    }

    private function reject(OrderContainer $orderContainer, string $message)
    {
        if ($orderContainer->isDebtorLimitLocked()) {
            $this->limitsService->unlock(
                $orderContainer->getMerchantDebtor(),
                $orderContainer->getOrder()->getAmountGross()
            );
        }

        $this->workflow->apply($orderContainer->getOrder(), OrderStateManager::TRANSITION_DECLINE);
        $this->orderRepository->update($orderContainer->getOrder());

        $this->logInfo("Order declined because of $message");
    }

    private function approve(OrderContainer $orderContainer)
    {
        $this->workflow->apply($orderContainer->getOrder(), OrderStateManager::TRANSITION_CREATE);
        $this->orderRepository->update($orderContainer->getOrder());

        $this->eventDispatcher->dispatch(OrderApprovedEvent::NAME, new OrderApprovedEvent($orderContainer));
    }

    private function triggerV2DebtorIdentificationAsync(OrderEntity $order, ?DebtorCompany $identifiedDebtorCompany): void
    {
        $data = [
            'order_id' => $order->getId(),
            'v1_company_id' => $identifiedDebtorCompany ? $identifiedDebtorCompany->getId() : null,
        ];

        try {
            $this->producer->publish(json_encode($data), 'order_debtor_identification_v2_paella');
        } catch (\Exception $exception) {
            $this->logSuppressedException($exception, 'Rabbit producer exception', ['data' => $data]);
        }
    }
}
