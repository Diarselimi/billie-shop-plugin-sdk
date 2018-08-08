<?php

namespace App\Application\UseCase\CreateOrder;

use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Alfred\DebtorDTO;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntityFactory;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\RiskCheck\Checker\CheckResult;
use Symfony\Component\Workflow\Workflow;

/**
 * @TODO: refactor the whole class
 */
class CreateOrderUseCase implements LoggingInterface
{
    use LoggingTrait;

    private $orderPersistenceService;
    private $orderChecksRunnerService;
    private $alfred;
    private $merchantDebtorRepository;
    private $merchantRepository;
    private $merchantDebtorFactory;
    private $orderRepository;
    private $workflow;

    public function __construct(
        OrderPersistenceService $orderPersistenceService,
        OrderChecksRunnerService $orderChecksRunnerService,
        AlfredInterface $alfred,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantRepositoryInterface $merchantRepository,
        MerchantDebtorEntityFactory $merchantDebtorFactory,
        OrderRepositoryInterface $orderRepository,
        Workflow $workflow
    ) {
        $this->orderPersistenceService = $orderPersistenceService;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->alfred = $alfred;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantRepository = $merchantRepository;
        $this->merchantDebtorFactory = $merchantDebtorFactory;
        $this->orderRepository = $orderRepository;
        $this->workflow = $workflow;
    }

    public function execute(CreateOrderRequest $request): void
    {
        $orderContainer = $this->orderPersistenceService->persistFromRequest($request);

        if (!$this->orderChecksRunnerService->runPreconditionChecks($orderContainer)) {
            $this->reject($orderContainer, 'preconditions checks failed');

            return;
        }

        $debtorDTO = $this->retrieveDebtor($orderContainer, $request);
        $debtorIdentified = $debtorDTO !== null;
        $this->orderChecksRunnerService->publishCheckResult(
            new CheckResult($debtorIdentified, 'debtor_identified', []),
            $orderContainer
        );

        if ($debtorDTO === null) {
            $this->reject($orderContainer, "debtor couldn't be identified");

            return;
        }

        $orderContainer->setDebtorCompany($debtorDTO);
        $this->orderRepository->update($orderContainer->getOrder());

        $this->logWaypoint('limit check');
        $limitLocked = $this->alfred->lockDebtorLimit(
            $orderContainer->getMerchantDebtor()->getDebtorId(),
            $orderContainer->getOrder()->getAmountGross()
        );

        $this->orderChecksRunnerService->publishCheckResult(
            new CheckResult($limitLocked, 'limit', []),
            $orderContainer
        );

        if (!$limitLocked) {
            $this->reject($orderContainer, "debtor limit exceeded");

            return;
        }

        if (!$this->orderChecksRunnerService->runChecks($orderContainer, $debtorDTO->getCrefoId())) {
            $this->alfred->unlockDebtorLimit(
                $orderContainer->getMerchantDebtor()->getDebtorId(),
                $orderContainer->getOrder()->getAmountGross()
            );
            $this->reject($orderContainer, 'checks failed');

            return;
        }

        $this->approve($orderContainer);
    }

    private function retrieveDebtor(OrderContainer $orderContainer, CreateOrderRequest $request): ?DebtorDTO
    {
        $merchantId = $request->getMerchantCustomerId();
        $debtor = $this->merchantDebtorRepository->getOneByExternalId($merchantId);
        $this->logWaypoint('known customer check');

        if (!$debtor) {
            $this->logInfo('Start the debtor identification');
            $debtorDTO = $this->identifyDebtor($orderContainer);

            if ($debtorDTO) {
                $this->logInfo('Debtor identified');
                $debtor = $this->merchantDebtorFactory->createFromDebtorDTO(
                    $debtorDTO,
                    $merchantId,
                    $request->getMerchantId()
                );
                $this->merchantDebtorRepository->insert($debtor);
            } else {
                $this->logInfo('Debtor could not be identification');

                return null;
            }
        } else {
            $this->logInfo('Debtor already known');
            $debtorDTO = $this->alfred->getDebtor($debtor->getDebtorId());
        }

        $orderContainer
            ->setMerchantDebtor($debtor)
            ->getOrder()->setMerchantDebtorId($debtor->getId());

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
            'tax_id' => $orderContainer->getDebtorExternalData()->getTaxId(),
            'tax_number' => $orderContainer->getDebtorExternalData()->getTaxNumber(),
            'registration_number' => $orderContainer->getDebtorExternalData()->getRegistrationNumber(),
            'registration_court' => $orderContainer->getDebtorExternalData()->getRegistrationCourt(),
            'legal_form' => $orderContainer->getDebtorExternalData()->getLegalForm(),
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

        $customer = $orderContainer->getMerchant();
        $customer->reduceAvailableFinancingLimit($orderContainer->getOrder()->getAmountGross());
        $this->merchantRepository->update($customer);

        $this->logInfo("Order approved!");
    }
}
