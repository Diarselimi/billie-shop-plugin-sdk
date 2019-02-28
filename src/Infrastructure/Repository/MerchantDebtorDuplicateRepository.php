<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\MerchantDebtor\MerchantDebtorDuplicateEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorDuplicateRepositoryInterface;

class MerchantDebtorDuplicateRepository extends AbstractRepository implements MerchantDebtorDuplicateRepositoryInterface
{
    public function upsert(MerchantDebtorDuplicateEntity $merchantDebtorDuplicate): MerchantDebtorDuplicateEntity
    {
        $id = $this->doInsert('
            INSERT INTO merchants_debtors_duplicates
            (main_merchant_debtor_id, duplicated_merchant_debtor_id, created_at, updated_at)
            VALUES
            (:main_merchant_debtor_id, :duplicated_merchant_debtor_id, :created_at, :updated_at)
            ON DUPLICATE KEY UPDATE updated_at = :updated_at
        ', [
            'main_merchant_debtor_id' => $merchantDebtorDuplicate->getMainMerchantDebtorId(),
            'duplicated_merchant_debtor_id' => $merchantDebtorDuplicate->getDuplicatedMerchantDebtorId(),
            'created_at' => $merchantDebtorDuplicate->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $merchantDebtorDuplicate->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        // get actual data
        $row = $this->doFetchOne('SELECT * FROM merchants_debtors_duplicates WHERE id = :id', ['id' => $id]);

        $merchantDebtorDuplicate
            ->setId((int) $id)
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']));

        return $merchantDebtorDuplicate;
    }
}
