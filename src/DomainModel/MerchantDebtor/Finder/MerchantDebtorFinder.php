<?php

namespace App\DomainModel\MerchantDebtor\Finder;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorCompany\IdentifyDebtorRequestFactory;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRegistrationService;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderStateManager;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class MerchantDebtorFinder implements LoggingInterface
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

    public function findDebtor(OrderContainer $orderContainer): MerchantDebtorFinderResult
    {
        $merchantId = $orderContainer->getOrder()->getMerchantId();
        $debtorExternalData = $orderContainer->getDebtorExternalData();
        $identifyRequest = $this->identifyDebtorRequestFactory->createDebtorRequestDTO($orderContainer);

        $this->logInfo('Check if the merchant debtor already known');
        $merchantDebtor = $this->merchantDebtorRepository->getOneByExternalIdAndMerchantId(
            $debtorExternalData->getMerchantExternalId(),
            $merchantId,
            [OrderStateManager::STATE_NEW, OrderStateManager::STATE_DECLINED]
        );

        if ($merchantDebtor) {
            $this->logInfo('Found the existing merchant debtor', ['id' => $merchantDebtor->getId()]);
            $identifyRequest->setCompanyId((int) $merchantDebtor->getDebtorId());
        } else {
            $hash = $debtorExternalData->getDataHash();

            $existingDebtorExternalData = $this->debtorExternalDataRepository->getOneByHashAndStateNotOlderThanDays(
                $hash,
                $debtorExternalData->getMerchantExternalId(),
                $merchantId,
                $debtorExternalData->getId(),
                OrderStateManager::STATE_DECLINED
            );

            if ($existingDebtorExternalData) {
                $this->logInfo('The debtor is with the same data and not older than 30 days found!');

                $merchantDebtor = $this->merchantDebtorRepository->getOneByExternalIdAndMerchantId(
                    $existingDebtorExternalData->getMerchantExternalId(),
                    $merchantId,
                    []
                );

                if (!$merchantDebtor) {
                    return new MerchantDebtorFinderResult();
                }

                $identifyRequest->setCompanyId($merchantDebtor->getDebtorId());
                $this->logInfo('Used the existing company id for identifying.');
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

            return new MerchantDebtorFinderResult();
        }

        $this->logInfo('Debtor identified');

        $merchantDebtor = $this->merchantDebtorRepository->getOneByMerchantAndCompanyUuid(
            $merchantId,
            $debtorCompany->getUuid()
        );

        if ($merchantDebtor) {
            $this->logInfo('Merchant debtor already exists');
        } else {
            $this->logInfo('New merchant debtor created');

            $merchantDebtor = $this->merchantDebtorRegistrationService->registerMerchantDebtor(
                $debtorCompany,
                $orderContainer->getMerchant()
            );
        }

        return new MerchantDebtorFinderResult($merchantDebtor, $debtorCompany);
    }
}
