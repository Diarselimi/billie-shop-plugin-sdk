<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantEntityFactory;
use App\DomainModel\Merchant\PartnerIdentifier;
use App\DomainModel\Merchant\MerchantRepository;
use App\Support\TwoWayEncryption\Encryptor;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class MerchantPdoRepository extends AbstractPdoRepository implements MerchantRepository
{
    public const TABLE_NAME = "merchants";

    private const SELECT_FIELDS = [
        'id',
        'name',
        'api_key',
        'oauth_client_id',
        'company_id',
        'company_uuid',
        'payment_merchant_id',
        'klarna_identifier',
        'sandbox_payment_merchant_id',
        'sepa_b2b_document_uuid',
        'is_active',
        'sepa_b2b_document_uuid',
        'financing_power',
        'available_financing_limit',
        'webhook_url',
        'webhook_authorization',
        'created_at',
        'updated_at',
        'investor_uuid',
    ];

    private MerchantEntityFactory $factory;

    private Encryptor $encryptor;

    public function __construct(MerchantEntityFactory $factory, Encryptor $encryptor)
    {
        $this->factory = $factory;
        $this->encryptor = $encryptor;
    }

    public function insert(MerchantEntity $merchant): void
    {
        $id = $this->doInsert('
            INSERT INTO ' . self::TABLE_NAME . '
            (name, api_key, oauth_client_id, is_active, financing_power, available_financing_limit, 
                company_id, company_uuid, sepa_b2b_document_uuid, payment_merchant_id, klarna_identifier, webhook_url, webhook_authorization, investor_uuid, created_at, updated_at)
            VALUES
            (:name, :api_key, :oauth_client_id, :is_active, :financing_power, :available_financing_limit, 
                :company_id, :company_uuid, :sepa_b2b_document_uuid, :payment_merchant_id, :klarna_identifier, :webhook_url, :webhook_authorization, :investor_uuid, :created_at, :updated_at)
        ', [
            'name' => $merchant->getName(),
            'company_uuid' => $merchant->getCompanyUuid(),
            'api_key' => $this->encryptor->encrypt($merchant->getApiKey()),
            'oauth_client_id' => $merchant->getOauthClientId(),
            'is_active' => $merchant->isActive(),
            'financing_power' => $merchant->getFinancingPower()->getMoneyValue(),
            'available_financing_limit' => $merchant->getFinancingPower()->getMoneyValue(),
            'company_id' => $merchant->getCompanyId(),
            'payment_merchant_id' => $merchant->getPaymentUuid(),
            'klarna_identifier' => (string) $merchant->getPartnerIdentifier(),
            'sepa_b2b_document_uuid' => $merchant->getSepaB2BDocumentUuid(),
            'webhook_url' => $merchant->getWebhookUrl(),
            'webhook_authorization' => $merchant->getWebhookAuthorization(),
            'investor_uuid' => $merchant->getInvestorUuid(),
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
              sepa_b2b_document_uuid = :document_uuid,
              updated_at = :updated_at
            WHERE id = :id
        ', [
            'id' => $merchant->getId(),
            'financing_power' => $merchant->getFinancingPower()->getMoneyValue(),
            'available_financing_limit' => $merchant->getFinancingLimit()->getMoneyValue(),
            'sandbox_payment_merchant_id' => $merchant->getSandboxPaymentUuid(),
            'document_uuid' => $merchant->getSepaB2BDocumentUuid(),
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

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function getOneByCompanyId(int $companyId): ?MerchantEntity
    {
        $row = $this->doFetchOne($this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS) . '
          WHERE company_id = :company_id
        ', ['company_id' => $companyId]);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function getOneByCompanyUuid(string $companyUuid): ?MerchantEntity
    {
        $row = $this->doFetchOne(
            $this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS) .
            ' WHERE company_uuid = :company_uuid ',
            ['company_uuid' => $companyUuid]
        );

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

    public function getOneByMerchantOnboardingId(int $merchantOnboardingId): ?MerchantEntity
    {
        $selectFields = array_map(function ($selectField) {
            return 'merchants.' . $selectField;
        }, self::SELECT_FIELDS);

        $row = $this->doFetchOne("
            SELECT " . implode(',', $selectFields) . "
            FROM " . self::TABLE_NAME . "
            INNER JOIN merchant_onboardings ON (
                merchants.id = merchant_onboardings.merchant_id
                AND merchant_onboardings.id = :merchantOnboardingId
            )
        ", ['merchantOnboardingId' => $merchantOnboardingId]);

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

        return $rows ? $this->factory->createFromArrayMultiple($rows) : null;
    }

    /**
     * @return MerchantEntity[]
     */
    public function getMerchantsWithoutSandbox(): array
    {
        $rows = $this->doFetchAll(
            $this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS) .
            ' WHERE sandbox_payment_merchant_id IS NULL'
        );

        return $rows ? $this->factory->createFromArrayMultiple($rows) : [];
    }

    public function getOneByPaymentUuid(string $paymentUuid): ?MerchantEntity
    {
        $row = $this->doFetchOne($this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS) . '
          WHERE payment_merchant_id = :paymentUuid
        ', ['paymentUuid' => $paymentUuid]);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function getByPartnerIdentifier(PartnerIdentifier $identifier): ?MerchantEntity
    {
        $r = $this->doFetchOne($this->generateSelectQuery(
            self::TABLE_NAME,
            self::SELECT_FIELDS
        ) . ' WHERE klarna_identifier = :identifier', [
            'identifier' => (string) $identifier,
        ]);

        return $r ? $this->factory->createFromArray($r) : null;
    }
}
