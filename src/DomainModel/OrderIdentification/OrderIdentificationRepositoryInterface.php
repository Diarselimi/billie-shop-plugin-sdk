<?php

namespace App\DomainModel\OrderIdentification;

interface OrderIdentificationRepositoryInterface
{
    public function insert(OrderIdentificationEntity $orderIdentificationEntity): void;

    public function findOneByOrderAndCompanyIds(int $orderId, int $v1CompanyId, int $v2CompanyId): ? array;
}
