<?php

namespace App\DomainModel\MerchantUser;

interface MerchantUserRoleRepositoryInterface
{
    public function create(MerchantUserRoleEntity $entity): void;

    public function getOneByUuid(string $uuid, int $merchantId = null): ?MerchantUserRoleEntity;

    public function getOneById(int $id, int $merchantId = null): ?MerchantUserRoleEntity;

    /**
     * @param  int                                 $merchantId
     * @return \Generator|MerchantUserRoleEntity[]
     */
    public function findAllByMerchantId(int $merchantId): \Generator;
}
