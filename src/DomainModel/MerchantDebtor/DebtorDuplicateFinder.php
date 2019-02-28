<?php

namespace App\DomainModel\MerchantDebtor;

use App\DomainModel\Order\OrderRepositoryInterface;

class DebtorDuplicateFinder
{
    private $orderRepository;

    /**
     * @var MerchantDebtorRepositoryInterface
     */
    private $debtorRepository;

    public function __construct(MerchantDebtorRepositoryInterface $debtorRepository, OrderRepositoryInterface $orderRepository)
    {
        $this->debtorRepository = $debtorRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return MerchantDebtorDuplicateDTO[]|\Generator
     */
    public function find(): \Generator
    {
        $grouped = $this->getDuplicatedDebtorsGrouped();

        foreach ($grouped as $merchantId => $externalDebtors) {
            foreach ($externalDebtors as $externalId => $debtors) {
                $mainDebtor = $this->resolveMainDebtor($debtors);

                if (is_null($mainDebtor)) {
                    throw new \RuntimeException("Main debtor cannot be found for merchant/external_ID {$merchantId}/{$externalId}");
                }

                foreach ($debtors as $i => $debtor) {
                    /** @var MerchantDebtorDuplicateDTO $debtor */
                    $isMainDebtor = $debtor->getDebtorId() === $mainDebtor->getDebtorId();
                    $debtor->setMarkAsDuplicate(false);
                    $orders = $debtor->getOrderStateCounter();

                    if ($isMainDebtor) {
                        yield $debtor;

                        continue;
                    }

                    $duplicationCategory = 0;

                    // 1 = only new or declined orders
                    if (($orders->getTotalNew() + $orders->getTotalDeclined()) === $orders->getTotal()) {
                        $duplicationCategory = 1;
                    }

                    // 2 = has inactive orders but not active
                    if (($orders->getTotalActive() == 0) && ($orders->getTotalInactive() > 0)) {
                        $duplicationCategory = 2;
                    }

                    // 3 = has active orders
                    if ($orders->getTotalActive() > 0) {
                        $duplicationCategory = 3;
                    }

                    $debtor->setDuplicationCategory($duplicationCategory);
                    $debtor->setParentDebtorId($mainDebtor->getDebtorId());
                    $debtor->setParentMerchantDebtorId($mainDebtor->getMerchantDebtorId());

                    $debtor->setMarkAsDuplicate($duplicationCategory !== 3);

                    yield $debtor;
                }
            }
        }
    }

    private function getDuplicatedDebtorsGrouped(): array
    {
        $grouped = [];

        // group by merchant ID and external code
        foreach ($this->debtorRepository->getDebtorsWithExternalId() as $debtor) {
            $dto = (new MerchantDebtorDuplicateDTO())
                ->setMerchantDebtorId($debtor->getMerchantDebtorId())
                ->setMerchantId($debtor->getMerchantId())
                ->setDebtorId($debtor->getDebtorId())
                ->setMerchantExternalId($debtor->getMerchantExternalId());

            $grouped[$dto->getMerchantId()][$dto->getMerchantExternalId()][] = $dto;
        }

        // remove the ones that do not have duplicates
        foreach ($grouped as $merchantId => $externalDebtors) {
            foreach ($externalDebtors as $externalId => $debtors) {
                if (count($debtors) <= 1) {
                    unset($grouped[$merchantId][$externalId]);
                }
            }
        }

        // add order counters
        foreach ($grouped as $merchantId => $externalDebtors) {
            foreach ($externalDebtors as $externalId => $debtors) {
                foreach ($debtors as $i => $debtor) {
                    /** @var MerchantDebtorDuplicateDTO $debtor */
                    $counter = $this->orderRepository->countOrdersByState($debtor->getMerchantDebtorId());
                    $debtor->setOrderStateCounter($counter);
                }
            }
        }

        return $grouped;
    }

    /**
     * @param  MerchantDebtorDuplicateDTO[]    $debtors
     * @return MerchantDebtorDuplicateDTO|null
     */
    private function resolveMainDebtor(array $debtors): ?MerchantDebtorDuplicateDTO
    {
        /** @var MerchantDebtorDuplicateDTO $mainDebtor */
        $mainDebtor = null;

        // FIRST, find the newest debtor with active orders
        foreach ($debtors as $i => $debtor) {
            $totalActive = $debtor->getOrderStateCounter()->getTotalActive();
            $isNewerDebtor = !is_null($mainDebtor) && ($debtor->getDebtorId() > $mainDebtor->getDebtorId());

            if ((is_null($mainDebtor) && $totalActive > 0) || (($totalActive > 0) && $isNewerDebtor)) {
                $mainDebtor = $debtor;
            }
        }

        // IF no debtors with active orders, FIND the newest debtor with inactive orders
        if (is_null($mainDebtor)) {
            foreach ($debtors as $i => $debtor) {
                $totalInactive = $debtor->getOrderStateCounter()->getTotalInactive();
                $isNewerDebtor = !is_null($mainDebtor) && ($debtor->getDebtorId() > $mainDebtor->getDebtorId());

                if ((is_null($mainDebtor) && $totalInactive > 0) || (($totalInactive > 0) && $isNewerDebtor)) {
                    $mainDebtor = $debtor;
                }
            }
        }

        // IF no debtors with active or inactive orders, FIND debtor that is the newest debtor
        if (is_null($mainDebtor)) {
            foreach ($debtors as $i => $debtor) {
                $isNewerDebtor = !is_null($mainDebtor) && ($debtor->getDebtorId() > $mainDebtor->getDebtorId());

                if (is_null($mainDebtor) || $isNewerDebtor) {
                    $mainDebtor = $debtor;
                }
            }
        }

        return $mainDebtor;
    }
}
