<?php

namespace App\DomainModel\CheckoutSession;

use App\Helper\Uuid\UuidGeneratorInterface;

class CheckoutSessionFactory
{
    private $uuidGenerator;

    public function __construct(UuidGeneratorInterface $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    public function createFromRequest(string $merchantExternalId, int $merchantId): CheckoutSessionEntity
    {
        $entity = (new CheckoutSessionEntity())
            ->setMerchantDebtorExternalId($merchantExternalId)
            ->setMerchantId($merchantId)
            ->setIsActive(true)
            ->setUuid($this->uuidGenerator->uuid4());

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
