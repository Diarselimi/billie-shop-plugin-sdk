<?php

namespace App\DomainModel\MerchantDebtor;

interface MerchantDebtorRepositoryInterface
{
    public function insert(MerchantDebtorEntity $merchantDebtor): void;

    public function update(MerchantDebtorEntity $merchantDebtor): void;

    public function getOneById(int $id): ?MerchantDebtorEntity;

    public function getOneByMerchantAndDebtorId(string $merchantId, string $debtorId): ?MerchantDebtorEntity;

    public function getOneByMerchantExternalId(string $externalMerchantId, string $merchantId, array $excludedOrderStates): ?MerchantDebtorEntity;

    public function getMerchantDebtorCreatedOrdersAmount(int $merchantDebtorId): float;

    /**
     * @return MerchantDebtorIdentifierDTO[]|\Generator
     */
    public function getDebtorsWithExternalId(string $where = ''): \Generator;
}
