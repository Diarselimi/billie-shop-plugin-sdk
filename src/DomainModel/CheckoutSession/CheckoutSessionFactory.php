<?php

namespace App\DomainModel\CheckoutSession;

use Ramsey\Uuid\Uuid;

class CheckoutSessionFactory
{
    public function createFromRequest(string $merchantExternalId, int $merchantId): CheckoutSessionEntity
    {
        $entity = (new CheckoutSessionEntity())
            ->setMerchantDebtorExternalId($merchantExternalId)
            ->setMerchantId($merchantId)
            ->setIsActive(true)
            ->setUuid(Uuid::uuid4()->toString());

        return $entity;
    }

    public function createFromArray(array $row): CheckoutSessionEntity
    {
        $entity = new CheckoutSessionEntity();
        $entity->setUuid($row['uuid'])
            ->setMerchantDebtorExternalId($row['merchant_debtor_external_id'])
            ->setMerchantId($row['merchant_id'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
            ->setIsActive(boolval($row['is_active']))
            ->setId($row['id']);

        return $entity;
    }
}
