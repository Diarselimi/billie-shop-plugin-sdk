<?php

namespace App\DomainModel\Order;

use Generator;

interface OrderRepositoryInterface
{
    public function insert(OrderEntity $order): void;

    public function update(OrderEntity $order): void;

    public function getOneByExternalCode(string $externalCode, int $merchantId): ?OrderEntity;

    public function getOneByMerchantIdAndExternalCodeOrUUID(string $id, int $merchantId): ? OrderEntity;

    public function getOneByPaymentId(string $paymentId): ?OrderEntity;

    public function getOneById(int $id): ?OrderEntity;

    public function getOneByUuid(string $uuid): ?OrderEntity;

    public function getDebtorMaximumOverdue(int $debtorId): int;

    public function getWithInvoiceNumber(int $limit, int $lastId = 0): Generator;

    public function debtorHasAtLeastOneFullyPaidOrder(int $debtorId): bool;

    public function merchantDebtorHasAtLeastOneApprovedOrder(int $debtorId): bool;

    public function countOrdersByState(int $merchantDebtorId): OrderStateCounterDTO;

    /**
     * @return Generator|array|OrderEntity[]
     */
    public function getOrdersByInvoiceHandlingStrategy(string $strategy): Generator;
}
