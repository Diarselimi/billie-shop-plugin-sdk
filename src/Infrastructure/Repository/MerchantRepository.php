<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantEntityFactory;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class MerchantRepository extends AbstractPdoRepository implements MerchantRepositoryInterface
{
    const SELECT_FIELDS = 'id, name, api_key, company_id, payment_merchant_id, roles, is_active, available_financing_limit, webhook_url, webhook_authorization, created_at, updated_at';

    private $factory;

    public function __construct(MerchantEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(MerchantEntity $merchant): void
    {
        $id = $this->doInsert('
            INSERT INTO merchants
            (name, api_key, roles, is_active, available_financing_limit, company_id, payment_merchant_id, webhook_url, webhook_authorization, created_at, updated_at)
            VALUES
            (:name, :api_key, :roles, :is_active, :available_financing_limit, :company_id, :payment_merchant_id, :webhook_url, :webhook_authorization, :created_at, :updated_at)
        ', [
            'name' => $merchant->getName(),
            'api_key' => $merchant->getApiKey(),
            'roles' => $merchant->getRoles(),
            'is_active' => $merchant->isActive(),
            'available_financing_limit' => $merchant->getAvailableFinancingLimit(),
            'company_id' => $merchant->getCompanyId(),
            'payment_merchant_id' => $merchant->getPaymentMerchantId(),
            'webhook_url' => $merchant->getWebhookUrl(),
            'webhook_authorization' => $merchant->getWebhookAuthorization(),
            'created_at' => $merchant->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $merchant->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);

        $merchant->setId($id);
    }

    public function update(MerchantEntity $merchant): void
    {
        $merchant->setUpdatedAt(new \DateTime());
        $this->doUpdate('
            UPDATE merchants
            SET 
              available_financing_limit = :available_financing_limit, 
              updated_at = :updated_at
            WHERE id = :id
        ', [
            'id' => $merchant->getId(),
            'available_financing_limit' => $merchant->getAvailableFinancingLimit(),
            'updated_at' => $merchant->getUpdatedAt()->format(self::DATE_FORMAT),
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

    public function getOneByCompanyId(int $companyId): ?MerchantEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM merchants
          WHERE company_id = :company_id
        ', ['company_id' => $companyId]);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }

    public function getOneByApiKey(string $apiKey): ?MerchantEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM merchants
          WHERE api_key = :api_key
        ', ['api_key' => $apiKey]);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }
}
