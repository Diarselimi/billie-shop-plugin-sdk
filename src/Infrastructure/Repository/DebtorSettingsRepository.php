<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\DebtorSettings\DebtorSettingsEntity;
use App\DomainModel\DebtorSettings\DebtorSettingsEntityFactory;
use App\DomainModel\DebtorSettings\DebtorSettingsRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class DebtorSettingsRepository extends AbstractPdoRepository implements DebtorSettingsRepositoryInterface
{
    public const TABLE_NAME = "debtor_settings";

    private const SELECT_FIELDS = 'id, company_uuid, is_whitelisted, created_at, updated_at';

    private $factory;

    public function __construct(DebtorSettingsEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(DebtorSettingsEntity $debtorSettings): void
    {
        $id = $this->doInsert('
            INSERT INTO ' . self::TABLE_NAME . '
            (company_uuid, is_whitelisted, created_at, updated_at)
            VALUES
            (:company_uuid, :is_whitelisted, :created_at, :updated_at)
        ', [
            'company_uuid' => $debtorSettings->getCompanyUuid(),
            'is_whitelisted' => (int) $debtorSettings->isWhitelisted(),
            'created_at' => $debtorSettings->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $debtorSettings->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);

        $debtorSettings->setId($id);
    }

    public function update(DebtorSettingsEntity $debtorSettings): void
    {
        $debtorSettings->setUpdatedAt(new \DateTime());

        $this->doUpdate('
            UPDATE ' . self::TABLE_NAME . '
            SET is_whitelisted = :whitelisted, updated_at = :updated_at
            WHERE id = :id
        ', [
            'id' => $debtorSettings->getId(),
            'whitelisted' => (int) $debtorSettings->isWhitelisted(),
            'updated_at' => $debtorSettings->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);
    }

    public function getOneByCompanyUuId(string $companyUuid): ?DebtorSettingsEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM ' . self::TABLE_NAME . '
          WHERE company_uuid = :company_uuid
        ', [
            'company_uuid' => $companyUuid,
        ]);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }
}
