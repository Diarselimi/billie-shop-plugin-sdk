<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantEntityFactory;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class MerchantRepository extends AbstractPdoRepository implements MerchantRepositoryInterface
{
    public const TABLE_NAME = "merchants";

    private const SELECT_FIELDS = [
        'id', 'name', 'api_key', 'oauth_client_id', 'company_id', 'company_uuid', 'payment_merchant_id', 'sandbox_payment_merchant_id',
        'is_active', 'financing_power', 'available_financing_limit', 'webhook_url', 'webhook_authorization', 'created_at', 'updated_at',
    ];

    private $factory;

    public function __construct(MerchantEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(MerchantEntity $merchant): void
    {
        $id = $this->doInsert('
            INSERT INTO ' . self::TABLE_NAME . '
            (name, api_key, oauth_client_id, is_active, financing_power, available_financing_limit, 
                company_id, company_uuid, payment_merchant_id, webhook_url, webhook_authorization, created_at, updated_at)
            VALUES
            (:name, :api_key, :oauth_client_id, :is_active, :financing_power, :available_financing_limit, 
                :company_id, :company_uuid, :payment_merchant_id, :webhook_url, :webhook_authorization, :created_at, :updated_at)
        ', [
            'name' => $merchant->getName(),
            'company_uuid' => $merchant->getCompanyUuid(),
            'api_key' => $merchant->getApiKey(),
            'oauth_client_id' => $merchant->getOauthClientId(),
            'is_active' => $merchant->isActive(),
            'financing_power' => $merchant->getFinancingPower(),
            'available_financing_limit' => $merchant->getFinancingPower(),
            'company_id' => $merchant->getCompanyId(),
            'payment_merchant_id' => $merchant->getPaymentUuid(),
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
            UPDATE ' . self::TABLE_NAME . '
            SET 
              financing_power = :financing_power, 
              available_financing_limit = :available_financing_limit, 
              sandbox_payment_merchant_id = :sandbox_payment_merchant_id,
              updated_at = :updated_at
            WHERE id = :id
        ', [
            'id' => $merchant->getId(),
            'financing_power' => $merchant->getFinancingPower(),
            'available_financing_limit' => $merchant->getFinancingLimit(),
            'sandbox_payment_merchant_id' => $merchant->getSandboxPaymentUuid(),
            'updated_at' => $merchant->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);
    }

    public function getOneById(int $id): ?MerchantEntity
    {
        $row = $this->doFetchOne($this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS) . '
          WHERE id = :id
        ', ['id' => $id]);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function getOneByUuid(string $paymentUuid): ?MerchantEntity
    {
        $row = $this->doFetchOne(
            $this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS) .
            ' WHERE payment_merchant_id = :payment_merchant_uuid ',
            ['payment_merchant_uuid' => $paymentUuid]
        );

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }

    public function getOneByCompanyId(int $companyId): ?MerchantEntity
    {
        $row = $this->doFetchOne($this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS) . '
          WHERE company_id = :company_id
        ', ['company_id' => $companyId]);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function getOneByApiKey(string $apiKey): ?MerchantEntity
    {
        $row = $this->doFetchOne($this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS) . '
          WHERE api_key = :api_key
        ', ['api_key' => $apiKey]);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function getOneByOauthClientId(string $oauthClientId): ?MerchantEntity
    {
        $row = $this->doFetchOne($this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS) . '
          WHERE oauth_client_id = :oauth_client_id
        ', ['oauth_client_id' => $oauthClientId]);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function findActiveWithFinancingPowerBelowPercentage(float $percentage): ?array
    {
        $rows = $this->doFetchAll(
            $this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS) .
            ' WHERE available_financing_limit IS NOT NULL ' .
            ' AND is_active = 1 ' .
            ' AND (financing_power / available_financing_limit * 100) < :percentage ',
            [
                'percentage' => $percentage,
            ]
        );

        return $rows ? $this->factory->createFromArrayCollection($rows) : null;
    }
}
