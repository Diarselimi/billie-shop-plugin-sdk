<?php

namespace App\DomainModel\Order;

use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;

class LimitsService
{
    private $companyService;

    private $merchantDebtorRepository;

    public function __construct(
        CompaniesServiceInterface $companyService,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository
    ) {
        $this->companyService = $companyService;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
    }

    public function lock(MerchantDebtorEntity $debtor, float $amount): bool
    {
        $companyLimitLocked = $this->lockCompanyLimit($debtor, $amount);
        if (!$companyLimitLocked) {
            return false;
        }

        $debtorLimitLocked = $this->lockDebtorLimit($debtor, $amount);
        if (!$debtorLimitLocked) {
            $this->unlockCompanyLimit($debtor, $amount);

            return false;
        }

        return true;
    }

    public function unlock(MerchantDebtorEntity $debtor, float $amount): bool
    {
        $companyLimitUnlocked = $this->unlockCompanyLimit($debtor, $amount);
        if (!$companyLimitUnlocked) {
            return false;
        }

        $debtorLimitUnlocked = $this->unlockDebtorLimit($debtor, $amount);
        if (!$debtorLimitUnlocked) {
            return false;
        }

        return true;
    }

    private function lockCompanyLimit(MerchantDebtorEntity $debtor, float $amount): bool
    {
        $debtorId = $debtor->getDebtorId();

        return $this->companyService->lockDebtorLimit($debtorId, $amount);
    }

    private function unlockCompanyLimit(MerchantDebtorEntity $debtor, float $amount): bool
    {
        $debtorId = $debtor->getDebtorId();

        //TODO: Handler failer unlock debtor limit
        $this->companyService->unlockDebtorLimit($debtorId, $amount);

        return true;
    }

    private function lockDebtorLimit(MerchantDebtorEntity $debtor, float $amount): bool
    {
        if (!$debtor->reduceFinancingLimit($amount)) {
            return false;
        }

        $this->merchantDebtorRepository->update($debtor);

        return true;
    }

    private function unlockDebtorLimit(MerchantDebtorEntity $debtor, float $amount): bool
    {
        if (!$debtor->increaseFinancingLimit($amount)) {
            return false;
        }

        $this->merchantDebtorRepository->update($debtor);

        return true;
    }
}
