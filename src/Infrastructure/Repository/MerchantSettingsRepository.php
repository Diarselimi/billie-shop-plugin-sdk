<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntityFactory;
use App\DomainModel\MerchantSettings\MerchantSettingsNotFoundException;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;

class MerchantSettingsRepository implements MerchantSettingsRepositoryInterface
{
    const SELECT_FIELDS = 'id, merchant_id, debtor_financing_limit, min_order_amount, created_at, updated_at';

    private $factory;

    public function __construct(MerchantSettingsEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function getOneByMerchant(int $merchantId): ?MerchantSettingsEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM merchant_settings
          WHERE merchant_id = :merchant_id
        ', ['merchant_id' => $merchantId]);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function getOneByMerchantOrFail(int $merchantId): MerchantSettingsEntity
    {
        $found = $this->getOneByMerchant($merchantId);

        if (!$found) {
            throw new MerchantSettingsNotFoundException();
        }

        return $found;
    }
}
