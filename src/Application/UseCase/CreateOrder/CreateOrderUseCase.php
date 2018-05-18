<?php

namespace App\Application\UseCase\CreateOrder;

use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Alfred\DebtorDTO;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Company\CompanyEntityFactory;
use App\DomainModel\Company\CompanyRepositoryInterface;
use App\DomainModel\Customer\CustomerRepositoryInterface;
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
    private $customerRepository;
    private $companyFactory;
    private $orderRepository;
    private $workflow;

    public function __construct(
        OrderPersistenceService $orderPersistenceService,
        OrderChecksRunnerService $orderChecksRunnerService,
        AlfredInterface $alfred,
        BorschtInterface $borscht,
        CompanyRepositoryInterface $companyRepository,
        CustomerRepositoryInterface $customerRepository,
        CompanyEntityFactory $companyEntityFactory,
        OrderRepositoryInterface $orderRepository,
        Workflow $workflow
    ) {
        $this->orderPersistenceService = $orderPersistenceService;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->alfred = $alfred;
        $this->companyRepository = $companyRepository;
        $this->customerRepository = $customerRepository;
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

        $debtorDTO = $this->retrieveDebtor($orderContainer, $request);
        if (is_null($debtorDTO)) {
            $this->reject($orderContainer, "debtor couldn't identified");

            return;
        }

        $this->orderRepository->update($orderContainer->getOrder());

        if (!$this->alfred->lockDebtorLimit($orderContainer->getCompany()->getDebtorId(), $orderContainer->getOrder()->getAmountGross())) {
            $this->reject($orderContainer, "debtor limit exceeded");

            return;
        }

        if (!$this->orderChecksRunnerService->runChecks($orderContainer, $debtorDTO->getCrefoId())) {
            $this->alfred->unlockDebtorLimit($orderContainer->getCompany()->getDebtorId(), $orderContainer->getOrder()->getAmountGross());
            $this->reject($orderContainer, 'checks failed');

            return;
        }

        $this->approve($orderContainer);
    }

    private function retrieveDebtor(OrderContainer $orderContainer, CreateOrderRequest $request): ?DebtorDTO
    {
        $merchantId = $request->getMerchantCustomerId();
        $debtor = $this->companyRepository->getOneByMerchantId($merchantId);

        if (!$debtor) {
            $debtorDTO = $this->identifyDebtor($orderContainer);

            if ($debtorDTO) {
                $debtor = $this->companyFactory->createFromDebtorDTO($debtorDTO, $merchantId);
                $this->companyRepository->insert($debtor);
            } else {
                return null;
            }
        } else {
            $debtorDTO = $this->alfred->getDebtor($debtor->getDebtorId());
        }

        $orderContainer
            ->setCompany($debtor)
            ->getOrder()->setCompanyId($debtor->getId())
        ;

        return $debtorDTO;
    }

    private function identifyDebtor(OrderContainer $orderContainer)
    {
        return $this->alfred->identifyDebtor([
            'name' => $orderContainer->getDebtorExternalData()->getName(),
            'address_house' => $orderContainer->getDebtorExternalDataAddress()->getHouseNumber(),
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

        $customer = $orderContainer->getCustomer();
        $customer->reduceAvailableFinancingLimit($orderContainer->getOrder()->getAmountGross());
        $this->customerRepository->update($customer);

        $this->logInfo("Order approved!");
    }
}
