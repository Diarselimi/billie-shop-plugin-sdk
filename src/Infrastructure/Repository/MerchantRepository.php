<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantEntityFactory;
use App\DomainModel\Merchant\MerchantRepositoryInterface;

class MerchantRepository extends AbstractRepository implements MerchantRepositoryInterface
{
    const SELECT_FIELDS = 'id, name, api_key, company_id, roles, is_active, available_financing_limit, webhook_url, created_at, updated_at';

    private $factory;

    public function __construct(MerchantEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(MerchantEntity $merchant): void
    {
        $id = $this->doInsert('
            INSERT INTO merchants
            (name, api_key, roles, is_active, available_financing_limit, created_at, updated_at, company_id, webhook_url)
            VALUES
            (:name, :api_key, :roles, :is_active, :available_financing_limit, :created_at, :updated_at, :company_id, :webhook_url)
        ', [
            'name' => $merchant->getName(),
            'api_key' => $merchant->getApiKey(),
            'roles' => $merchant->getRoles(),
            'is_active' => $merchant->isActive(),
            'available_financing_limit' => $merchant->getAvailableFinancingLimit(),
            'created_at' => $merchant->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $merchant->getUpdatedAt()->format('Y-m-d H:i:s'),
            'company_id' => $merchant->getCompanyId(),
            'webhook_url' => $merchant->getWebhookUrl(),
        ]);

        $merchant->setId($id);
    }

    public function update(MerchantEntity $merchant): void
    {
        $merchant->setUpdatedAt(new \DateTime());
        $this->doUpdate('
            UPDATE merchants
            SET available_financing_limit = :available_financing_limit, updated_at = :updated_at
            WHERE id = :id
        ', [
            'id' => $merchant->getId(),
            'available_financing_limit' => $merchant->getAvailableFinancingLimit(),
            'updated_at' => $merchant->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function getOneById(int $id): ?MerchantEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM merchants
          WHERE id = :id
        ', ['id' => $id]);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }

    public function getOneByApiKeyRaw(string $apiKey): ?array
    {
        $customer = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM merchants
          WHERE api_key = :api_key
        ', ['api_key' => $apiKey]);

        return $customer ?: null;
    }
}
