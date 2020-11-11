<?php

namespace App\DomainModel\MerchantDebtor\Finder;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\IdentifyDebtorRequestFactory;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRegistrationService;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\Support\DateFormat;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class MerchantDebtorFinder implements LoggingInterface
{
    use LoggingTrait;

    private const DEBTOR_HASH_MAX_MINUTES = 60;

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
            [OrderEntity::STATE_NEW, OrderEntity::STATE_DECLINED]
        );

        if ($merchantDebtor) {
            $this->logInfo(
                'Found an existing merchant debtor by merchant external id',
                [LoggingInterface::KEY_ID => $merchantDebtor->getId()]
            );
            $identifyRequest->setCompanyId((int) $merchantDebtor->getDebtorId());
            $identifyRequest->setCompanyUuid($merchantDebtor->getCompanyUuid());
        } else {
            $hash = $debtorExternalData->getDataHash();

            $existingDebtorExternalData = $this->debtorExternalDataRepository
                ->getOneByHashAndStateNotOlderThanMaxMinutes(
                    $hash,
                    $debtorExternalData->getMerchantExternalId(),
                    $merchantId,
                    $debtorExternalData->getId(),
                    OrderEntity::STATE_DECLINED,
                    self::DEBTOR_HASH_MAX_MINUTES
                );

            if ($existingDebtorExternalData) {
                $this->logInfo(
                    'A recent debtor external data hash has been found. '
                    . 'Trying to find the merchant debtor using the merchant external ID...',
                    [
                        LoggingInterface::KEY_SOBAKA => [
                            'external_data_id' => $existingDebtorExternalData->getId(),
                            'external_data_timestamp' => $existingDebtorExternalData->getCreatedAt()->format(DateFormat::FORMAT_YMD_HIS),
                        ],
                    ]
                );

                $merchantDebtor = $this->merchantDebtorRepository->getOneByExternalIdAndMerchantId(
                    $existingDebtorExternalData->getMerchantExternalId(),
                    $merchantId,
                    []
                );

                if (!$merchantDebtor) {
                    $this->logInfo(
                        'Merchant Debtor was NOT found using the merchant external ID.'
                    );

                    return new MerchantDebtorFinderResult();
                }

                $identifyRequest->setCompanyId($merchantDebtor->getDebtorId());
                $identifyRequest->setCompanyUuid($merchantDebtor->getCompanyUuid());
                $this->logInfo(
                    'Merchant Debtor found using external ID. The associated company will be used for identification.',
                    [LoggingInterface::KEY_UUID => $merchantDebtor->getCompanyUuid()]
                );
            } else {
                $this->logInfo('Try to identify new debtor');
            }
        }

        $this->logInfo('Starting {name} debtor identification algorithm for order {number}', [
            LoggingInterface::KEY_NAME => $identifyRequest->isExperimental() ? 'experimental' : 'normal',
            LoggingInterface::KEY_NUMBER => $orderContainer->getOrder()->getExternalCode(),
        ]);

        $identifyDebtorResponseDTO = $this->companiesService->identifyDebtor($identifyRequest);
        $debtorCompany = $identifyDebtorResponseDTO->getIdentifiedDebtorCompany();
        if (!$debtorCompany) {
            $this->logInfo('Debtor could not be identified');

            return new MerchantDebtorFinderResult(null, null, $identifyDebtorResponseDTO->getMostSimilarCandidate());
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

        return new MerchantDebtorFinderResult(
            $merchantDebtor,
            $debtorCompany,
            $identifyDebtorResponseDTO->getMostSimilarCandidate()
        );
    }
}
