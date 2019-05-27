<?php

namespace App\Application\UseCase\CreateOrder;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\MerchantDebtor\DebtorFinder;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateOrderUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $orderPersistenceService;

    private $orderChecksRunnerService;

    private $orderRepository;

    private $debtorFinderService;

    private $validator;

    private $producer;

    private $orderResponseFactory;

    private $merchantDebtorFinancialDetailsRepository;

    private $orderStateManager;

    private $checkoutSessionRepository;

    public function __construct(
        OrderPersistenceService $orderPersistenceService,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderRepositoryInterface $orderRepository,
        DebtorFinder $debtorFinderService,
        ValidatorInterface $validator,
        ProducerInterface $producer,
        OrderResponseFactory $orderResponseFactory,
        MerchantDebtorFinancialDetailsRepositoryInterface $merchantDebtorFinancialDetailsRepository,
        OrderStateManager $orderStateManager,
        CheckoutSessionRepositoryInterface $checkoutSessionRepository
    ) {
        $this->orderPersistenceService = $orderPersistenceService;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->orderRepository = $orderRepository;
        $this->debtorFinderService = $debtorFinderService;
        $this->validator = $validator;
        $this->producer = $producer;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->merchantDebtorFinancialDetailsRepository = $merchantDebtorFinancialDetailsRepository;
        $this->orderStateManager = $orderStateManager;
        $this->checkoutSessionRepository = $checkoutSessionRepository;
    }

    public function execute(CreateOrderRequest $request): OrderContainer
    {
        $this->validateRequest($request);

        $orderContainer = $this->orderPersistenceService->persistFromRequest($request);
        $isOrderFromCheckout = $request->getCheckoutSessionId() !== null;

        if (!$this->orderChecksRunnerService->runPreIdentificationChecks($orderContainer)) {
            $this->orderStateManager->decline($orderContainer);

            return $orderContainer;
        }

        if ($this->identifyDebtor($orderContainer, $request->getMerchantId()) !== null) {
            $this->orderRepository->update($orderContainer->getOrder());
        }

        if (!$this->orderChecksRunnerService->runPostIdentificationChecks($orderContainer)) {
            $this->orderStateManager->decline($orderContainer);

            return $orderContainer;
        }

        if ($this->orderChecksRunnerService->checkForFailedSoftDeclinableCheckResults($orderContainer)) {
            $isOrderFromCheckout ? $this->orderStateManager->decline($orderContainer) : $this->orderStateManager->wait($orderContainer);

            return $orderContainer;
        }

        if ($isOrderFromCheckout) {
            $this->orderStateManager->authorize($orderContainer);
            $this->checkoutSessionRepository->invalidateById($orderContainer->getOrder()->getCheckoutSessionId());

            return $orderContainer;
        }

        $this->orderStateManager->approve($orderContainer);

        return $orderContainer;
    }

    private function identifyDebtor(OrderContainer $orderContainer, int $merchantId): ?MerchantDebtorEntity
    {
        $merchantDebtor = $this->debtorFinderService->findDebtor($orderContainer, $merchantId);

        if (!$orderContainer->getMerchantSettings()->useExperimentalDebtorIdentification()) {
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
            ->setMerchantDebtorFinancialDetails($this->merchantDebtorFinancialDetailsRepository->getCurrentByMerchantDebtorId($merchantDebtor->getId()))
            ->getOrder()->setMerchantDebtorId($merchantDebtor->getId());

        return $merchantDebtor;
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
