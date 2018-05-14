<?php

namespace App\Application\UseCase\CreateOrder;

use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Company\CompanyEntity;
use App\DomainModel\Company\CompanyEntityFactory;
use App\DomainModel\Company\CompanyRepositoryInterface;
use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use Symfony\Component\Workflow\Workflow;

class CreateOrderUseCase implements LoggingInterface
{
    use LoggingTrait;

    private $orderPersistenceService;
    private $orderChecksRunnerService;
    private $alfred;
    private $companyRepository;
    private $companyFactory;
    private $orderRepository;
    private $workflow;

    public function __construct(
        OrderPersistenceService $orderPersistenceService,
        OrderChecksRunnerService $orderChecksRunnerService,
        AlfredInterface $alfred,
        BorschtInterface $borscht,
        CompanyRepositoryInterface $companyRepository,
        CompanyEntityFactory $companyEntityFactory,
        OrderRepositoryInterface $orderRepository,
        Workflow $workflow
    ) {
        $this->orderPersistenceService = $orderPersistenceService;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->alfred = $alfred;
        $this->companyRepository = $companyRepository;
        $this->companyFactory = $companyEntityFactory;
        $this->orderRepository = $orderRepository;
        $this->workflow = $workflow;
    }

    public function execute(CreateOrderRequest $request): void
    {
        $orderContainer = $this->orderPersistenceService->persistFromRequest($request);

        if (!$this->orderChecksRunnerService->runPreconditionChecks($orderContainer)) {
            $this->reject($orderContainer, 'preconditions checks failed');
        }

        $debtor = $this->retrieveDebtor($orderContainer, $request);
        if (!$debtor) {
            $this->reject($orderContainer, "debtor couldn't identified");

            return;
        }

        $orderContainer->getOrder()->setCompanyId($debtor->getId());
        $this->orderRepository->updateCompany($orderContainer->getOrder());

        if (!$this->alfred->lockDebtorLimit($debtor->getDebtorId(), $orderContainer->getOrder()->getAmountGross())) {
            $this->reject($orderContainer, 'debtor limit exceeded');

            return;
        }

        if (!$this->orderChecksRunnerService->runChecks($orderContainer)) {
            $this->alfred->unlockDebtorLimit($debtor->getDebtorId(), $orderContainer->getOrder()->getAmountGross());
            $this->reject($orderContainer, 'checks failed');

            return;
        }

        $this->approve($orderContainer);
    }

    private function retrieveDebtor(OrderContainer $orderContainer, CreateOrderRequest $request): ?CompanyEntity
    {
        $merchantId = $request->getMerchantCustomerId();
        $debtor = $this->companyRepository->getOneByMerchantId($merchantId);

        if (!$debtor) {
            $debtorDTO = $this->identifyDebtor($orderContainer);

            if ($debtorDTO) {
                $debtor = $this->companyFactory->createFromDebtorDTO($debtorDTO, $merchantId);
                $this->companyRepository->insert($debtor);
            }
        }

        return $debtor;
    }

    private function identifyDebtor(OrderContainer $orderContainer)
    {
        return $this->alfred->identifyDebtor([
            'name' => $orderContainer->getDebtorExternalData()->getName(),
            'address_house' => $orderContainer->getDebtorExternalData()->getName(),
            'address_street' => $orderContainer->getDebtorExternalDataAddress()->getStreet(),
            'address_postal_code' => $orderContainer->getDebtorExternalDataAddress()->getPostalCode(),
            'address_city' => $orderContainer->getDebtorExternalDataAddress()->getCity(),
            'address_country' => $orderContainer->getDebtorExternalDataAddress()->getCountry(),
        ]);
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

        $this->logInfo("Order approved!");
    }
}
