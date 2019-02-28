<?php

namespace App\DomainModel\MerchantDebtor;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;

class DebtorDuplicateHandler
{
    private $duplicatesRepository;

    private $companiesService;

    public function __construct(
        MerchantDebtorDuplicateRepositoryInterface $duplicatesRepository,
        CompaniesServiceInterface $companiesService
    ) {
        $this->duplicatesRepository = $duplicatesRepository;
        $this->companiesService = $companiesService;
    }

    public function register(MerchantDebtorDuplicateDTO $duplicate): bool
    {
        if (
            !$duplicate->isMarkAsDuplicate() ||
            is_null($duplicate->getParentMerchantDebtorId()) ||
            is_null($duplicate->getParentDebtorId())
        ) {
            return false;
        }
        $now = new \DateTime();
        $duplicateEntity = (new MerchantDebtorDuplicateEntity())
            ->setMainMerchantDebtorId($duplicate->getParentMerchantDebtorId())
            ->setDuplicatedMerchantDebtorId($duplicate->getMerchantDebtorId())
            ->setCreatedAt($now)
            ->setUpdatedAt($now);

        $duplicateEntity = $this->duplicatesRepository->upsert($duplicateEntity);

        // it's a new duplicate if both dates are the same
        if (
            $duplicateEntity->getCreatedAt()->getTimestamp() ===
            $duplicateEntity->getUpdatedAt()->getTimestamp()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Communicate duplicates to other services
     *
     * @param MerchantDebtorDuplicateDTO[] $duplicates
     * @param int                          $batchSize
     */
    public function broadcast(array $duplicates, int $batchSize = 100): void
    {
        $currentBatch = [];

        while (!empty($duplicates)) {
            for ($i = 0; ($i < $batchSize) || ($i < (count($duplicates) - 1)); $i++) {
                $duplicate = array_shift($duplicates);
                if ($duplicate instanceof MerchantDebtorDuplicateDTO) {
                    $currentBatch[] = $duplicate;
                }
            }
            if (!empty($currentBatch)) {
                $this->companiesService->markDuplicates($currentBatch);
            }
        }
    }
}
