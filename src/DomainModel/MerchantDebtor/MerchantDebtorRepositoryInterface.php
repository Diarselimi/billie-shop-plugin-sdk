<?php

namespace App\DomainModel\MerchantDebtor;

interface MerchantDebtorRepositoryInterface
{
    public function insert(MerchantDebtorEntity $merchantDebtor): void;

    public function getOneById(int $id): ?MerchantDebtorEntity;

    public function getOneByUuid(string $uuid): ?MerchantDebtorEntity;

    public function getOneByUuidAndMerchantId(string $uuid, int $merchantId): ?MerchantDebtorEntity;

    public function getOneByMerchantAndCompanyUuid(string $merchantId, string $companyUuid): ?MerchantDebtorEntity;

    public function getOneByExternalIdAndMerchantId(string $externalMerchantId, string $merchantId, array $excludedOrderStates = []): ?MerchantDebtorEntity;

    public function getOneByUuidOrExternalIdAndMerchantId(string $uuidOrExternalID, int $merchantId): ?MerchantDebtorEntity;

    public function getMerchantDebtorCreatedOrdersAmount(int $merchantDebtorId): float;

    public function getMerchantDebtorOrdersAmountByState(int $merchantDebtorId, string $state): float;

    /**
     * @param  string                                   $where
     * @return MerchantDebtorIdentifierDTO[]|\Generator
     */
    public function getMerchantDebtorIdentifierDtos(string $where = ''): ?\Generator;

    public function getOneMerchantDebtorIdentifierDto(int $merchantDebtorId): ?MerchantDebtorIdentifierDTO;

    public function findExternalId(int $merchantDebtorId): ?string;

    /**
     * @param  int         $merchantId
     * @param  int         $offset
     * @param  int         $limit
     * @param  string      $sortBy
     * @param  string      $sortDirection
     * @param  string|null $searchString
     * @return array       Array with 'total' and 'rows' containing 'id' and 'external_id'
     */
    public function getByMerchantId(
        int $merchantId,
        int $offset,
        int $limit,
        string $sortBy,
        string $sortDirection,
        ?string $searchString
    ): array;

    /**
     * @param  int                    $debtorCompanyId
     * @return MerchantDebtorEntity[]
     */
    public function getManyByDebtorCompanyId(int $debtorCompanyId): array;
}
