<?php

namespace App\DomainModel\Order;

use Generator;
use Symfony\Component\HttpFoundation\ParameterBag;

interface OrderRepositoryInterface
{
    public function insert(OrderEntity $order): void;

    public function update(OrderEntity $order): void;

    public function getOneByExternalCode(string $externalCode, int $merchantId): ?OrderEntity;

    public function getAuthorizedByCheckoutSessionUuid(string $checkoutSessionUuid): ?OrderEntity;

    public function getOneByMerchantIdAndExternalCodeOrUUID(string $id, int $merchantId): ? OrderEntity;

    public function getOneByPaymentId(string $paymentId): ?OrderEntity;

    public function getOneById(int $id): ?OrderEntity;

    public function getOneByUuid(string $uuid): ?OrderEntity;

    public function getDebtorMaximumOverdue(string $companyUuid): int;

    public function debtorHasAtLeastOneFullyPaidOrder(string $companyUuid): bool;

    public function merchantDebtorHasAtLeastOneApprovedOrder(int $merchantDebtorId): bool;

    public function countOrdersByState(int $merchantDebtorId): OrderStateCounterDTO;

    /**
     * @return Generator|array|OrderEntity[]
     */
    public function getOrdersByInvoiceHandlingStrategy(string $strategy): Generator;

    public function search(
        int $merchantId,
        int $offset,
        int $limit,
        string $sortBy,
        string $sortDirection,
        ?string $searchString,
        array $filters
    ): array;

    public function getOrdersCountByMerchantDebtorAndState(int $merchantDebtorId, string $state): int;
}
