<?php

namespace App\DomainModel\MerchantUser;

interface MerchantUserRepositoryInterface
{
    public function create(MerchantUserEntity $merchantUserEntity): void;

    public function getOneByUuid(string $uuid): ? MerchantUserEntity;
}
