<?php

namespace App\DomainModel\Order;

use Generator;

interface OrderRepositoryInterface
{
    public function insert(OrderEntity $order): void;

    public function update(OrderEntity $order): void;

    public function getOneByExternalCode(string $externalCode, int $customerId): ?OrderEntity;

    public function getOneByPaymentId(string $paymentId): ?OrderEntity;

    public function getOneById(int $id): ?OrderEntity;

    public function getOneByUuid(string $uuid): ?OrderEntity;

    public function getCustomerOverdues(int $merchantDebtorId): Generator;

    public function getWithInvoiceNumber(int $limit, int $lastId = 0): Generator;

    public function debtorHasAtLeastOneFullyPaidOrder(int $debtorId): bool;

    public function countOrdersByState(int $merchantDebtorId): OrderStateCounterDTO;
}
