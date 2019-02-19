<?php

namespace App\Application\UseCase\CreateOrder;

use App\Application\Exception\RequestValidationException;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\DebtorFinderService;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\Order\LimitsService;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\RiskCheck\Checker\CheckResult;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\Workflow\Workflow;

class CreateOrderUseCase implements LoggingInterface
{
    use LoggingTrait;

    private $orderPersistenceService;

    private $orderChecksRunnerService;

    private $merchantRepository;

    private $orderRepository;

    private $workflow;

    private $limitsService;

    private $debtorFinderService;

    private $validator;

    private $producer;

    public function __construct(
        OrderPersistenceService $orderPersistenceService,
        OrderChecksRunnerService $orderChecksRunnerService,
        MerchantRepositoryInterface $merchantRepository,
        OrderRepositoryInterface $orderRepository,
        Workflow $workflow,
        LimitsService $limitsService,
        DebtorFinderService $debtorFinderService,
        ValidatorInterface $validator,
        ProducerInterface $producer
    ) {
        $this->orderPersistenceService = $orderPersistenceService;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->merchantRepository = $merchantRepository;
        $this->orderRepository = $orderRepository;
        $this->workflow = $workflow;
        $this->limitsService = $limitsService;
        $this->debtorFinderService = $debtorFinderService;
        $this->validator = $validator;
        $this->producer = $producer;
    }

    public function execute(CreateOrderRequest $request): void
    {
        $this->validateRequest($request);

        $orderContainer = $this->orderPersistenceService->persistFromRequest($request);

        if (!$this->orderChecksRunnerService->runPreconditionChecks($orderContainer)) {
            $this->reject($orderContainer, 'preconditions checks failed');

            return;
        }

        if ($this->identifyDebtor($orderContainer, $request->getMerchantId()) === null) {
            $this->reject($orderContainer, "debtor couldn't be identified");

            return;
        }

        $this->orderRepository->update($orderContainer->getOrder());

        if (!$this->checkLimit($orderContainer)) {
            $this->reject($orderContainer, "debtor limit exceeded");

            return;
        }

        if (!$this->orderChecksRunnerService->runChecks($orderContainer)) {
            $this->limitsService->unlock(
                $orderContainer->getMerchantDebtor(),
                $orderContainer->getOrder()->getAmountGross()
            );

            $this->reject($orderContainer, 'checks failed');

            return;
        }

        $this->approve($orderContainer);
    }

    private function validateRequest(CreateOrderRequest $request): void
    {
        $validationErrors = $this->validator->validate($request);

        if ($validationErrors->count() === 0) {
            return;
        }

        throw new RequestValidationException($validationErrors);
    }

    private function identifyDebtor(OrderContainer $orderContainer, int $merchantId): ? MerchantDebtorEntity
    {
        $merchantDebtor = $this->debtorFinderService->findDebtor($orderContainer, $merchantId);

        $this->triggerV2DebtorIdentification(
            $orderContainer->getOrder(),
            $merchantDebtor ? $merchantDebtor->getDebtorCompany() : null
        );

        if ($merchantDebtor === null) {
            $this->orderChecksRunnerService->persistCheckResult(
                new CheckResult(false, 'debtor_identified', ['debtor_found' => 0]),
                $orderContainer
            );

            return null;
        }

        $this->orderChecksRunnerService->persistCheckResult(
            new CheckResult(true, 'debtor_identified', [
                'debtor_found' => 1,
                'debor_company_id' => $merchantDebtor->getDebtorCompany()->getId(),
            ]),
            $orderContainer
        );

        $orderContainer
            ->setMerchantDebtor($merchantDebtor)
            ->getOrder()->setMerchantDebtorId($merchantDebtor->getId())
        ;

        return $merchantDebtor;
    }

    private function checkLimit(OrderContainer $orderContainer): bool
    {
        $this->logWaypoint('limit check');

        $limitsLocked = $this->limitsService->lock(
            $orderContainer->getMerchantDebtor(),
            $orderContainer->getOrder()->getAmountGross()
        );

        $this->orderChecksRunnerService->persistCheckResult(
            new CheckResult($limitsLocked, 'limit', []),
            $orderContainer
        );

        return $limitsLocked;
    }

    private function reject(OrderContainer $orderContainer, string $message)
    {
        $this->workflow->apply($orderContainer->getOrder(), OrderStateManager::TRANSITION_DECLINE);
        $this->orderRepository->update($orderContainer->getOrder());

        $this->logInfo("Order declined because of $message");
    }

    private function approve(OrderContainer $orderContainer)
    {
        $this->workflow->apply($orderContainer->getOrder(), OrderStateManager::TRANSITION_CREATE);
        $this->orderRepository->update($orderContainer->getOrder());

        $customer = $orderContainer->getMerchant();
        $customer->reduceAvailableFinancingLimit($orderContainer->getOrder()->getAmountGross());
        $this->merchantRepository->update($customer);

        $this->logInfo("Order approved!");
    }

    private function triggerV2DebtorIdentification(OrderEntity $order, ?DebtorCompany $identifiedDebtorCompany): void
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
