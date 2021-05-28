<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\CheckoutSession\CheckoutSessionEntity;
use App\DomainModel\CheckoutSession\CheckoutSessionFactory;
use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class CheckoutSessionRepository extends AbstractPdoRepository implements CheckoutSessionRepositoryInterface
{
    const TABLE_NAME = "checkout_sessions";

    const TABLE_FIELDS = "id, uuid, merchant_id, merchant_debtor_external_id, is_active, created_at, updated_at";

    private $checkoutSessionFactory;

    public function __construct(CheckoutSessionFactory $checkoutSessionFactory)
    {
        $this->checkoutSessionFactory = $checkoutSessionFactory;
    }

    public function create(CheckoutSessionEntity $entity): CheckoutSessionEntity
    {
        $sql = "
            INSERT INTO ".self::TABLE_NAME." 
            (`uuid`, `merchant_id`, `merchant_debtor_external_id`, `is_active`, `created_at`, `updated_at`) 
            VALUES(:uuid, :merchant, :merchant_debtor_external_id, :is_active, :created_at, :updated_at);";

        $attributes = [
            'uuid' => $entity->getUuid(),
            'merchant' => $entity->getMerchantId(),
            'merchant_debtor_external_id' => $entity->getMerchantDebtorExternalId(),
            'is_active' => (int) $entity->isActive(),
            'created_at' => $entity->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $entity->getUpdatedAt()->format(self::DATE_FORMAT),
        ];

        $id = $this->doInsert($sql, $attributes);
        $entity->setId($id);

        return $entity;
    }

    public function findOneById(int $id): ?CheckoutSessionEntity
    {
        $sql = "SELECT ".self::TABLE_FIELDS." FROM ".self::TABLE_NAME." WHERE id = :id";

        $row = $this->doFetchOne($sql, ['id' => $id]);

        return $row ? $this->checkoutSessionFactory->createFromArray($row) : null;
    }

    public function findOneByUuid(string $uuid): ?CheckoutSessionEntity
    {
        $sql = "SELECT ".self::TABLE_FIELDS." FROM ".self::TABLE_NAME." WHERE uuid = :uuid";

        $row = $this->doFetchOne($sql, ['uuid' => $uuid]);

        return $row ? $this->checkoutSessionFactory->createFromArray($row) : null;
    }

    public function invalidateById(int $id): bool
    {
        $sql = "UPDATE ".self::TABLE_NAME." SET is_active = 0 WHERE id = :id";

        return $this->doExecute($sql, ['id' => $id])->execute();
    }

    public function reActivateSession(string $sessionUuid): void
    {
        $sql = "UPDATE ".self::TABLE_NAME." SET is_active = 1 WHERE uuid = :uuid";

        $this->doExecute($sql, ['uuid' => $sessionUuid])->execute();
    }
}
