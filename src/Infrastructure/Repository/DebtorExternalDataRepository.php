<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntityFactory;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class DebtorExternalDataRepository extends AbstractPdoRepository implements DebtorExternalDataRepositoryInterface
{
    private const SELECT_FIELDS = 'id, name, tax_id, tax_number, registration_number, registration_court, legal_form, industry_sector, subindustry_sector, employees_number, address_id, is_established_customer, merchant_external_id, debtor_data_hash, created_at, updated_at';

    private $debtorExternalDataEntityFactory;

    public function __construct(DebtorExternalDataEntityFactory $debtorExternalDataEntityFactory)
    {
        $this->debtorExternalDataEntityFactory = $debtorExternalDataEntityFactory;
    }

    public function insert(DebtorExternalDataEntity $debtor): void
    {
        $id = $this->doInsert('
            INSERT INTO debtor_external_data
            (name, tax_id, tax_number, registration_number, registration_court, legal_form, industry_sector, subindustry_sector, employees_number, address_id, is_established_customer, merchant_external_id, created_at, updated_at, debtor_data_hash)
            VALUES
            (:name, :tax_id, :tax_number, :registration_number, :registration_court, :legal_form, :industry_sector, :subindustry_sector, :employees_number, :address_id, :is_established_customer, :merchant_external_id, :created_at, :updated_at, :debtor_data_hash)
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
            'is_established_customer' => $debtor->isEstablishedCustomer(),
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
            'SELECT ' . self::SELECT_FIELDS . ' FROM '. DebtorExternalDataEntity::TABLE_NAME .' WHERE id = :id',
            ['id' => $id]
        );

        return $debtorRowData ? $this->debtorExternalDataEntityFactory->createFromDatabaseRow($debtorRowData) : null;
    }

    public function getOneByHashAndNotOlderThanDays(string $hash, int $ignoreId, int $days = 30): ?DebtorExternalDataEntity
    {
        $debtorExternalData = $this->doFetchOne(
            'SELECT '. self::SELECT_FIELDS .' FROM '. DebtorExternalDataEntity::TABLE_NAME .
            '   WHERE debtor_data_hash = :hash 
                AND id != :id
                AND DATE_ADD(created_at, INTERVAL :days DAY) > NOW()
                ORDER BY created_at DESC LIMIT 1',
            ['hash' => $hash, 'days' => $days, 'id' => $ignoreId]
        );

        return $debtorExternalData ? $this->debtorExternalDataEntityFactory->createFromDatabaseRow($debtorExternalData) : null;
    }
}
