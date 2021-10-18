<?php

namespace App\DomainModel\MerchantDebtor\Finder;

use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
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

    private MerchantDebtorRepositoryInterface $merchantDebtorRepository;

    private DebtorExternalDataRepositoryInterface $debtorExternalDataRepository;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
    }

    public function findDebtorBasedOnPreviousOrderCreationAttempts(
        OrderContainer $orderContainer
    ): MerchantDebtorFinderResult {
        $merchantId = $orderContainer->getOrder()->getMerchantId();
        $debtorExternalData = $orderContainer->getDebtorExternalData();

        if ($debtorExternalData->getMerchantExternalId() === null || empty($debtorExternalData->getMerchantExternalId())) {
            return new MerchantDebtorFinderResult();
        }

        $this->logInfo('Check if the merchant debtor already known');
        $merchantDebtor = $this->merchantDebtorRepository->getOneByExternalIdMerchantIdAndExludedOrderStates(
            $debtorExternalData->getMerchantExternalId(),
            $merchantId,
            [OrderEntity::STATE_NEW, OrderEntity::STATE_DECLINED]
        );

        if ($merchantDebtor !== null) {
            $this->logInfo(
                'Found an existing merchant debtor by merchant external id',
                [LoggingInterface::KEY_ID => $merchantDebtor->getId()]
            );

            return new MerchantDebtorFinderResult($merchantDebtor);
        }

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

        if ($existingDebtorExternalData === null) {
            return new MerchantDebtorFinderResult();
        }

        $this->logThatWeFoundPreviousAttemptsWithTheSameOrderData($existingDebtorExternalData);
        $merchantDebtor = $this->merchantDebtorRepository->getOneByExternalIdMerchantIdAndExludedOrderStates(
            $existingDebtorExternalData->getMerchantExternalId(),
            $merchantId,
            []
        );

        if ($merchantDebtor === null) {
            $this->logInfo(
                'Merchant Debtor was NOT found using the merchant external ID.'
            );

            return new MerchantDebtorFinderResult(null, null, null, true);
        }

        $this->logInfo(
            'Merchant Debtor found using external ID. The associated company will be used for identification.',
            [LoggingInterface::KEY_UUID => $merchantDebtor->getCompanyUuid()]
        );

        return new MerchantDebtorFinderResult($merchantDebtor);
    }

    private function logThatWeFoundPreviousAttemptsWithTheSameOrderData(
        DebtorExternalDataEntity $existingDebtorExternalData
    ): void {
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
    }
}
