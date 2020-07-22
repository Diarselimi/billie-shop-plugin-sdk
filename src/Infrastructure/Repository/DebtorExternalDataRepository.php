<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntityFactory;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class DebtorExternalDataRepository extends AbstractPdoRepository implements DebtorExternalDataRepositoryInterface
{
    public const TABLE_NAME = "debtor_external_data";

    public const INVALID_MERCHANT_EXTERNAL_ID_SUFFIX = 'invalidated';

    public const INVALID_DEBTOR_DATA_HASH = 'INVALID_HASH';

    private const SELECT_FIELDS =
        self::TABLE_NAME.'.id as id, 
        name, 
        tax_id, 
        tax_number, 
        registration_number, 
        registration_court, 
        legal_form, 
        industry_sector, 
        subindustry_sector, 
        employees_number, 
        address_id, 
        billing_address_id, 
        is_established_customer,
        merchant_external_id, 
        debtor_data_hash,
        '.self::TABLE_NAME.'.created_at as created_at, 
        '.self::TABLE_NAME.'.updated_at as updated_at'
    ;

    private $debtorExternalDataEntityFactory;

    public function __construct(DebtorExternalDataEntityFactory $debtorExternalDataEntityFactory)
    {
        $this->debtorExternalDataEntityFactory = $debtorExternalDataEntityFactory;
    }

    public function insert(DebtorExternalDataEntity $debtor): void
    {
        $id = $this->doInsert('
            INSERT INTO debtor_external_data
            (name, tax_id, tax_number, registration_number, registration_court, legal_form, industry_sector, subindustry_sector, employees_number, address_id, billing_address_id, is_established_customer, merchant_external_id, created_at, updated_at, debtor_data_hash)
            VALUES
            (:name, :tax_id, :tax_number, :registration_number, :registration_court, :legal_form, :industry_sector, :subindustry_sector, :employees_number, :address_id, :billing_address_id, :is_established_customer, :merchant_external_id, :created_at, :updated_at, :debtor_data_hash)
        ', [
            'name' => $debtor->getName(),
            'tax_id' => $debtor->getTaxId(),
            'tax_number' => $debtor->getTaxNumber(),
            'registration_number' => $debtor->getRegistrationNumber(),
            'registration_court' => $debtor->getRegistrationCourt(),
            'legal_form' => $debtor->getLegalForm(),
            'industry_sector' => $debtor->getIndustrySector(),
            'subindustry_sector' => $debtor->getSubindustrySector(),
            'employees_number' => $debtor->getEmployeesNumber(),
            'address_id' => $debtor->getAddressId(),
            'billing_address_id' => $debtor->getBillingAddressId(),
            'is_established_customer' => intval($debtor->isEstablishedCustomer()),
            'merchant_external_id' => $debtor->getMerchantExternalId(),
            'created_at' => $debtor->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $debtor->getUpdatedAt()->format(self::DATE_FORMAT),
            'debtor_data_hash' => $debtor->getDataHash(),
        ]);

        $debtor->setId($id);
    }

    public function getOneById(int $id): ?DebtorExternalDataEntity
    {
        $debtorRowData = $this->doFetchOne(
            'SELECT ' . self::SELECT_FIELDS . ' FROM '. self::TABLE_NAME .' WHERE id = :id',
            ['id' => $id]
        );

        return $debtorRowData ? $this->debtorExternalDataEntityFactory->createFromDatabaseRow($debtorRowData) : null;
    }

    public function getOneByHashAndStateNotOlderThanMaxMinutes(
        string $hash,
        string $merchantDebtorExternalId,
        int $merchantId,
        int $ignoreId,
        string $state,
        int $maxMinutes
    ): ?DebtorExternalDataEntity {
        $debtorExternalData = $this->doFetchOne(
            '
            SELECT ' . self::SELECT_FIELDS . ' FROM ' . self::TABLE_NAME . '
            INNER JOIN orders ON orders.debtor_external_data_id = ' . self::TABLE_NAME . '.id
            WHERE 
                orders.state = :state
                AND debtor_data_hash = :hash 
                AND orders.merchant_id = :merchant_id
                AND merchant_external_id = :merchant_debtor_external_id
                AND ' . self::TABLE_NAME . '.id != :id
                AND DATE_ADD( ' . self::TABLE_NAME . '.created_at, INTERVAL :minutes MINUTE) > :now
                ORDER BY  ' . self::TABLE_NAME . '.created_at DESC LIMIT 1',
            [
                'hash' => $hash,
                'minutes' => $maxMinutes,
                'id' => $ignoreId,
                'state' => $state,
                'merchant_id' => $merchantId,
                'merchant_debtor_external_id' => $merchantDebtorExternalId,
                'now' => date(self::DATE_FORMAT),
            ]
        );

        return $debtorExternalData ? $this->debtorExternalDataEntityFactory->createFromDatabaseRow(
            $debtorExternalData
        ) : null;
    }

    public function invalidateMerchantExternalIdAndDebtorHashForCompanyUuid(string $companyUuid): void
    {
        $sql = "
            UPDATE ". self::TABLE_NAME ." ded
            JOIN orders o ON o.debtor_external_data_id = ded.id
            JOIN merchants_debtors md ON md.id = o.merchant_debtor_id
            SET merchant_external_id = CONCAT_WS('-', merchant_external_id, :merchantExternalIdSuffix), debtor_data_hash = :invalidDebtorDataHash
            WHERE md.company_uuid = :companyUuid;
        ";

        $this->doUpdate(
            $sql,
            [
                'companyUuid' => $companyUuid,
                'merchantExternalIdSuffix' => self::INVALID_MERCHANT_EXTERNAL_ID_SUFFIX,
                'invalidDebtorDataHash' => self::INVALID_DEBTOR_DATA_HASH,
            ]
        );
    }

    public function update(DebtorExternalDataEntity $externalData): void
    {
        $externalData->setUpdatedAt(new \DateTime());

        $this->doUpdate('
            UPDATE ' . self::TABLE_NAME . '
            SET billing_address_id = :billing_address_id, updated_at = :updated_at
            WHERE id = :id
        ', [
            'id' => $externalData->getId(),
            'billing_address_id' => $externalData->getBillingAddressId(),
            'updated_at' => $externalData->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);
    }

    public function getMerchantDebtorExternalIds(int $merchantDebtorId): array
    {
        return $this->doFetchAll(
            '
            SELECT DISTINCT ' . self::TABLE_NAME.'.merchant_external_id as external_id' . ' FROM ' . self::TABLE_NAME . '
            INNER JOIN orders ON orders.debtor_external_data_id = ' . self::TABLE_NAME . '.id
            WHERE orders.merchant_debtor_id = :merchant_debtor_id
            ORDER BY  ' . self::TABLE_NAME . '.created_at DESC',
            [
                'merchant_debtor_id' => $merchantDebtorId,
            ]
        );
    }

    public function getOneByMerchantIdAndExternalCode(int $merchantId, string $externalCode): ?DebtorExternalDataEntity
    {
        $debtorExternalData = $this->doFetchOne(
            '
            SELECT ' . self::SELECT_FIELDS . ' FROM ' . self::TABLE_NAME . '
            INNER JOIN orders ON orders.debtor_external_data_id = ' . self::TABLE_NAME . '.id
            WHERE 
                orders.merchant_id = :merchant_id
                AND merchant_external_id = :merchant_debtor_external_id
                ORDER BY  ' . self::TABLE_NAME . '.created_at DESC LIMIT 1',
            [
                'merchant_id' => $merchantId,
                'merchant_debtor_external_id' => $externalCode,
            ]
        );

        return $debtorExternalData ? $this->debtorExternalDataEntityFactory->createFromDatabaseRow(
            $debtorExternalData
        ) : null;
    }
}
