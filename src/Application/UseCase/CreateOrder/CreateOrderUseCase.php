<?php

namespace App\Application\UseCase\CreateOrder;

use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Alfred\DebtorDTO;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
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

    private $borscht;

    public function __construct(
        OrderPersistenceService $orderPersistenceService,
        OrderChecksRunnerService $orderChecksRunnerService,
        AlfredInterface $alfred,
        BorschtInterface $borscht,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantRepositoryInterface $merchantRepository,
        MerchantDebtorEntityFactory $merchantDebtorFactory,
        OrderRepositoryInterface $orderRepository,
        Workflow $workflow
    ) {
        $this->orderPersistenceService = $orderPersistenceService;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->alfred = $alfred;
        $this->borscht = $borscht;
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
        if ($debtorDTO === null) {
            $this->orderChecksRunnerService->publishCheckResult(
                new CheckResult(false, 'debtor_identified', ['debtor_found' => 0]),
                $orderContainer
            );
            $this->reject($orderContainer, "debtor couldn't be identified");

            return;
        }

        $this->orderChecksRunnerService->publishCheckResult(
            new CheckResult(true, 'debtor_identified', [
                'debtor_found' => 1,
                'debor_company_id' => $debtorDTO->getId(),
            ]),
            $orderContainer
        );

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
        $this->logInfo('Start the debtor identification');
        $debtorDTO = $this->identifyDebtor($orderContainer);

        if (!$debtorDTO) {
            $this->logInfo('Debtor could not be identified');

            return null;
        }

        $this->logInfo('Debtor identified');

        $merchantId = $request->getMerchantId();
        $debtorId = $debtorDTO->getId();

        $merchantDebtor = $this->merchantDebtorRepository->getOneByMerchantAndDebtorId($merchantId, $debtorId);

        if ($merchantDebtor) {
            $this->logInfo('Debtor already known');
        } else {
            $this->logInfo('Register new Debtor');
            $merchantDebtor = $this->registerMerchantDebtor($debtorId, $orderContainer->getMerchant());
        }

        $orderContainer
            ->setMerchantDebtor($merchantDebtor)
            ->getOrder()->setMerchantDebtorId($merchantDebtor->getId())
        ;

        return $debtorDTO;
    }

    private function registerMerchantDebtor(string $debtorId, MerchantEntity $merchant): MerchantDebtorEntity
    {
        $paymentDebtor = $this->borscht->registerDebtor($merchant->getPaymentMerchantId());

        $merchantDebtor = $this->merchantDebtorFactory->create(
            $debtorId,
            $merchant->getId(),
            $paymentDebtor->getPaymentDebtorId()
        );

        $this->merchantDebtorRepository->insert($merchantDebtor);

        return $merchantDebtor;
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
            'first_name' => $orderContainer->getDebtorPerson()->getFirstName(),
            'last_name' => $orderContainer->getDebtorPerson()->getLastName(),
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
