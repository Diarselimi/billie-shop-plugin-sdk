<?php

namespace App\DomainModel\MerchantDebtor;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorCompany\IdentifyDebtorRequestFactory;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderStateManager;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class DebtorFinder implements LoggingInterface
{
    use LoggingTrait;

    private $merchantDebtorRepository;

    private $companiesService;

    private $merchantDebtorRegistrationService;

    private $debtorExternalDataRepository;

    private $identifyDebtorRequestFactory;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        CompaniesServiceInterface $companiesService,
        MerchantDebtorRegistrationService $merchantDebtorRegistrationService,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        IdentifyDebtorRequestFactory $identifyDebtorRequestFactory
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->companiesService = $companiesService;
        $this->merchantDebtorRegistrationService = $merchantDebtorRegistrationService;
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
        $this->identifyDebtorRequestFactory = $identifyDebtorRequestFactory;
    }

    public function findDebtor(OrderContainer $orderContainer, int $merchantId): ?MerchantDebtorEntity
    {
        $identifyRequest = $this->identifyDebtorRequestFactory->createDebtorRequestDTO(
            $orderContainer,
            $orderContainer->getMerchantSettings()->useExperimentalDebtorIdentification()
        );

        $this->logInfo('Check if the merchant debtor already known');
        $merchantDebtor = $this->merchantDebtorRepository->getOneByMerchantExternalId(
            $orderContainer->getDebtorExternalData()->getMerchantExternalId(),
            $merchantId,
            [OrderStateManager::STATE_NEW, OrderStateManager::STATE_DECLINED]
        );

        if ($merchantDebtor) {
            $this->logInfo('Found the existing merchant debtor', ['id' => $merchantDebtor->getId()]);
            $identifyRequest->setCompanyId((int) $merchantDebtor->getDebtorId());
        } else {
            $hash = $orderContainer->getDebtorExternalData()->getDataHash();

            $debtorExternalData = $this->debtorExternalDataRepository->getOneByHashAndStateNotOlderThanDays(
                $hash,
                $orderContainer->getDebtorExternalData()->getId(),
                OrderStateManager::STATE_DECLINED
            );

            if ($debtorExternalData) {
                $this->logInfo('The debtor is with the same data and not older than 30 days, identification process stopped...');

                return null;
            } else {
                $this->logInfo('Try to identify new debtor');
            }
        }

        $this->logInfo('Starting {algorithm} debtor identification algorithm for order {order_external_code}', [
            'algorithm' => $identifyRequest->isExperimental() ? 'experimental' : 'normal',
            'order_external_code' => $orderContainer->getOrder()->getExternalCode(),
        ]);

        /** @var DebtorCompany $debtorCompany */
        $debtorCompany = $this->companiesService->identifyDebtor($identifyRequest);
        if (!$debtorCompany) {
            $this->logInfo('Debtor could not be identified');

            return null;
        }

        $this->logInfo('Debtor identified');

        $merchantDebtor = $this->merchantDebtorRepository->getOneByMerchantAndDebtorId(
            $merchantId,
            $debtorCompany->getId()
        );

        if ($merchantDebtor) {
            $this->logInfo('Merchant debtor already exists');
        } else {
            $this->logInfo('New merchant debtor created');

            $merchantDebtor = $this->merchantDebtorRegistrationService->registerMerchantDebtor(
                $debtorCompany->getId(),
                $orderContainer->getMerchant()
            );
        }

        $merchantDebtor->setDebtorCompany($debtorCompany);

        return $merchantDebtor;
    }
}
