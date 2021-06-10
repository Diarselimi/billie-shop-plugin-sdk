<?php

namespace App\DomainModel\Order;

use Generator;

interface OrderRepositoryInterface
{
    public function insert(OrderEntity $order): void;

    public function update(OrderEntity $order): void;

    public function updateOrderExternalCode(OrderEntity $orderEntity): void;

    public function updateMerchantDebtor(int $orderId, int $merchantDebtorId): void;

    public function updateIdentificationBillingAddress(int $orderId, string $billingAddressUuid): void;

    public function getOneByExternalCodeAndMerchantId(string $externalCode, int $merchantId): ?OrderEntity;

    public function getNotYetConfirmedByCheckoutSessionUuid(string $checkoutSessionUuid): ?OrderEntity;

    public function getOneByMerchantIdAndExternalCodeOrUUID(string $id, int $merchantId): ?OrderEntity;

    public function getOneByMerchantIdAndUUID(string $uuid, int $merchantId): ?OrderEntity;

    public function getOneByPaymentId(string $paymentId): ?OrderEntity;

    public function getOneById(int $id): ?OrderEntity;

    public function getOneByUuid(string $uuid): ?OrderEntity;

    public function getDebtorMaximumOverdue(string $companyUuid): int;

    public function debtorHasAtLeastOneFullyPaidOrder(string $companyUuid): bool;

    public function countOrdersByState(int $merchantDebtorId): OrderStateCounterDTO;

    /**
     * @return OrderEntity[]
     */
    public function getByInvoice(string $invoiceUuid): array;

    public function getByInvoiceAndMerchant(string $invoiceUuid, int $merchantId): ?OrderEntity;

    /**
     * @return Generator|array|OrderEntity[]
     */
    public function getOrdersByInvoiceHandlingStrategy(string $strategy): Generator;

    public function getOrdersCountByMerchantDebtorAndState(int $merchantDebtorId, string $state): int;

    public function getOrdersCountByCompanyBillingAddressAndState(
        string $companyUuid,
        string $addressUuid,
        string $state
    ): int;

    public function updateDurationExtension(int $orderId, int $durationExtension): void;

    /**
     * @return OrderEntity[]
     */
    public function geOrdersByMerchantId(int $merchantId, \DateTime $shippedFrom, int $limit): array;
}
