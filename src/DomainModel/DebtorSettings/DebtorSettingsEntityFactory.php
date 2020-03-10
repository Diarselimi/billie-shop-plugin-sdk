<?php

namespace App\DomainModel\DebtorSettings;

class DebtorSettingsEntityFactory
{
    public function createFromDatabaseRow(array $row): DebtorSettingsEntity
    {
        return (new DebtorSettingsEntity())
            ->setId($row['id'])
            ->setCompanyUuid($row['company_uuid'])
            ->setIsWhitelisted(boolval($row['is_whitelisted']))
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']));
    }

    public function create(
        string $companyUuid,
        bool $isWhitelisted = false
    ): DebtorSettingsEntity {
        $now = new \DateTime();

        return (new DebtorSettingsEntity())
            ->setCompanyUuid($companyUuid)
            ->setIsWhitelisted($isWhitelisted)
            ->setCreatedAt($now)
            ->setUpdatedAt($now);
    }
}
