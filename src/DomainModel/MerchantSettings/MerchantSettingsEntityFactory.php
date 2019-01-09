<?php

namespace App\DomainModel\MerchantSettings;

class MerchantSettingsEntityFactory
{
    public function createFromArray(array $data): MerchantSettingsEntity
    {
        return (new MerchantSettingsEntity())
            ->setId((int) $data['id'])
            ->setCreatedAt(new \DateTime($data['created_at']))
            ->setUpdatedAt(new \DateTime($data['updated_at']))
            ->setMerchantId((int) $data['merchant_id'])
            ->setDebtorFinancingLimit((float) $data['debtor_financing_limit'])
            ->setMinOrderAmount((float) $data['min_order_amount'])
            ;
    }
}
