<?php

namespace App\Application\UseCase\CreateOrder;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantDebtor\Finder\MerchantDebtorFinder;
use App\DomainModel\Order\NewOrder\OrderPersistenceService;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class CreateOrderUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $persistNewOrderService;

    private $orderContainerFactory;

    private $orderChecksRunnerService;

    private $orderRepository;

    private $debtorFinderService;

    private $producer;

    private $orderResponseFactory;

    private $orderStateManager;

    private $merchantDebtorFinancialDetailsRepository;

    private $checkoutSessionRepository;

    public function __construct(
        OrderPersistenceService $persistNewOrderService,
        OrderContainerFactory $orderContainerFactory,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderRepositoryInterface $orderRepository,
        MerchantDebtorFinder $debtorFinderService,
        ProducerInterface $producer,
        OrderResponseFactory $orderResponseFactory,
        OrderStateManager $orderStateManager,
        CheckoutSessionRepositoryInterface $checkoutSessionRepository
    ) {
        $this->persistNewOrderService = $persistNewOrderService;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->orderRepository = $orderRepository;
        $this->debtorFinderService = $debtorFinderService;
        $this->producer = $producer;
        $this->orderResponseFactory = $orderResponseFactory;
        $this->orderStateManager = $orderStateManager;
        $this->checkoutSessionRepository = $checkoutSessionRepository;
    }

    public function execute(CreateOrderRequest $request): OrderContainer
    {
        $this->validateRequest($request);

        $newOrder = $this->persistNewOrderService->persistFromRequest($request);
        $orderContainer = $this->orderContainerFactory->createFromNewOrderDTO($newOrder);
        $isOrderFromCheckout = $request->getCheckoutSessionId() !== null;

        if ($isOrderFromCheckout) {
            $this->checkoutSessionRepository->invalidateById($orderContainer->getOrder()->getCheckoutSessionId());
        }

        if (!$this->orderChecksRunnerService->runPreIdentificationChecks($orderContainer)) {
            $this->orderStateManager->decline($orderContainer);

            return $orderContainer;
        }

        if ($this->identifyDebtor($orderContainer)) {
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

            return $orderContainer;
        }

        $this->orderStateManager->approve($orderContainer);

        return $orderContainer;
    }

    private function identifyDebtor(OrderContainer $orderContainer): bool
    {
        $debtorFinderResult = $this->debtorFinderService->findDebtor($orderContainer);

        //TODO: event
        if (!$orderContainer->getMerchantSettings()->useExperimentalDebtorIdentification()) {
            $this->triggerV2DebtorIdentificationAsync(
                $orderContainer->getOrder(),
                $debtorFinderResult->getDebtorCompany() ? $debtorFinderResult->getDebtorCompany() : null
            );
        }

        if ($debtorFinderResult->getMerchantDebtor() === null) {
            return false;
        }

        $orderContainer
            ->setMerchantDebtor($debtorFinderResult->getMerchantDebtor())
            ->setDebtorCompany($debtorFinderResult->getDebtorCompany())
        ;

        return true;
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
