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

    private const IDENTIFICATION_ALGORITHMS = [
        CompaniesServiceInterface::DEBTOR_IDENTIFICATION_ALGORITHM_V1 => 'identifyDebtor',
        CompaniesServiceInterface::DEBTOR_IDENTIFICATION_ALGORITHM_V2 => 'identifyDebtorV2',
    ];

    private $merchantDebtorRepository;

    private $companiesService;

    private $merchantDebtorRegistrationService;

    /**
     * @var DebtorExternalDataRepositoryInterface
     */
    private $debtorExternalDataRepository;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        CompaniesServiceInterface $companiesService,
        MerchantDebtorRegistrationService $merchantDebtorRegistrationService,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->companiesService = $companiesService;
        $this->merchantDebtorRegistrationService = $merchantDebtorRegistrationService;
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
    }

    public function findDebtor(OrderContainer $orderContainer, int $merchantId): ?MerchantDebtorEntity
    {
        $identifyRequest = (new IdentifyDebtorRequestFactory())->createDebtorRequestDTO($orderContainer);

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

        $identificationAlgorithmVersion = $orderContainer->getMerchantSettings()->getDebtorIdentificationAlgorithm();

        $this->logInfo('Starting debtor identification using {debtor_identification_algorithm_version} for order {order_external_code}', [
            'debtor_identification_algorithm_version' => $identificationAlgorithmVersion,
            'order_external_code' => $orderContainer->getOrder()->getExternalCode(),
        ]);

        /** @var DebtorCompany $debtorCompany */
        $debtorCompany = $this->companiesService->{self::IDENTIFICATION_ALGORITHMS[$identificationAlgorithmVersion]}($identifyRequest);
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
