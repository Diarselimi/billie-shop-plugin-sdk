<?php

namespace App\DomainModel\DebtorInformationChangeRequest;

use App\Support\AbstractFactory;

class DebtorInformationChangeRequestEntityFactory extends AbstractFactory
{
    public function createFromArray(array $row): DebtorInformationChangeRequestEntity
    {
        return (new DebtorInformationChangeRequestEntity())
            ->setUuid($row['uuid'])
            ->setCompanyUuid($row['company_uuid'])
            ->setName($row['name'])
            ->setCity($row['city'])
            ->setPostalCode($row['postal_code'])
            ->setStreet($row['street'])
            ->setHouseNumber($row['house_number'])
            ->setMerchantUserId($row['merchant_user_id'])
            ->setIsSeen((bool) $row['is_seen'])
            ->setState($row['state'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }

    public function createFromDatabaseRow(array $row): DebtorInformationChangeRequestEntity
    {
        return $this->createFromArray($row)
            ->setId($row['id'])
        ;
    }
}
