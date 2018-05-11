<?php

namespace App\Application\UseCase\CreateOrder;

use App\Application\PaellaCoreCriticalException as PaellaException;
use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Company\CompanyEntityFactory;
use App\DomainModel\Company\CompanyRepositoryInterface;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;

class CreateOrderUseCase
{
    private $orderPersistenceService;
    private $orderChecksRunnerService;
    private $alfred;
    private $companyRepository;
    private $companyFactory;
    private $orderRepository;

    public function __construct(
        OrderPersistenceService $orderPersistenceService,
        OrderChecksRunnerService $orderChecksRunnerService,
        AlfredInterface $alfred,
        BorschtInterface $borscht,
        CompanyRepositoryInterface $companyRepository,
        CompanyEntityFactory $companyEntityFactory,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderPersistenceService = $orderPersistenceService;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->alfred = $alfred;
        $this->companyRepository = $companyRepository;
        $this->companyFactory = $companyEntityFactory;
        $this->orderRepository = $orderRepository;
    }

    public function execute(CreateOrderRequest $request)
    {
        $orderContainer = $this->orderPersistenceService->persistFromRequest($request);

        if (!$this->orderChecksRunnerService->runPreconditionChecks($orderContainer)) {
            $this->reject($orderContainer, 'Preconditions checks failed', PaellaException::CODE_ORDER_PRECONDITION_CHECKS_FAILED);
        }

        $merchantId = $request->getMerchantCustomerId();
        $debtor = $this->companyRepository->getOneByMerchantId($merchantId);

        if (!$debtor) {
            $debtorDTO = $this->identifyDebtor($orderContainer);

            if ($debtorDTO) {
                $debtor = $this->companyFactory->createFromDebtorDTO($debtorDTO, $merchantId);
                $this->companyRepository->insert($debtor);
            }
        }

        if (!$debtor) {
            $this->reject($orderContainer, "Debtor couldn't identified", PaellaException::CODE_DEBTOR_COULD_NOT_BE_IDENTIFIED);
        }

        $orderContainer->getOrder()->setCompanyId($debtor->getId());
        $this->orderRepository->updateCompany($orderContainer->getOrder());

        if (!$this->alfred->lockDebtorLimit($debtor->getDebtorId(), $orderContainer->getOrder()->getAmountGross())) {
            $this->reject($orderContainer, 'Debtor limit exceeded', PaellaException::CODE_DEBTOR_LIMIT_EXCEEDED);
        }

        if (!$this->orderChecksRunnerService->runChecks($orderContainer)) {
            $this->alfred->unlockDebtorLimit($debtor->getDebtorId(), $orderContainer->getOrder()->getAmountGross());
            $this->reject($orderContainer, 'Checks failed', PaellaException::CODE_ORDER_CHECKS_FAILED);
        }

        // approve order
    }

    private function identifyDebtor(OrderContainer $order)
    {
        return $this->alfred->identifyDebtor([
            'name' => $order->getDebtorExternalData()->getName(),
            'address_house' => $order->getDebtorExternalData()->getName(),
            'address_street' => $order->getDebtorExternalDataAddress()->getStreet(),
            'address_postal_code' => $order->getDebtorExternalDataAddress()->getPostalCode(),
            'address_city' => $order->getDebtorExternalDataAddress()->getCity(),
            'address_country' => $order->getDebtorExternalDataAddress()->getCountry(),
        ]);
    }

    private function reject(OrderContainer $order, string $message, string $code)
    {
        // reject the order
        throw new PaellaException($message, $code);
    }
}
