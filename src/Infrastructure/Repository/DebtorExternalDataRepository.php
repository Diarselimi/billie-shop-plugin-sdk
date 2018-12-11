<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntityFactory;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;

class DebtorExternalDataRepository extends AbstractRepository implements DebtorExternalDataRepositoryInterface
{
    private $debtorExternalDataEntityFactory;

    public function __construct(DebtorExternalDataEntityFactory $debtorExternalDataEntityFactory)
    {
        $this->debtorExternalDataEntityFactory = $debtorExternalDataEntityFactory;
    }

    public function insert(DebtorExternalDataEntity $debtor): void
    {
        $id = $this->doInsert('
            INSERT INTO debtor_external_data
            (name, tax_id, tax_number, registration_number, registration_court, legal_form, industry_sector, subindustry_sector, employees_number, address_id, is_established_customer, merchant_external_id, created_at, updated_at)
            VALUES
            (:name, :tax_id, :tax_number, :registration_number, :registration_court, :legal_form, :industry_sector, :subindustry_sector, :employees_number, :address_id, :is_established_customer, :merchant_external_id, :created_at, :updated_at)
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
            'created_at' => $debtor->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $debtor->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $debtor->setId($id);
    }

    public function getOneByIdRaw(int $id): ?array
    {
        $address = $this->doFetchOne('SELECT * FROM debtor_external_data WHERE id = :id', [
            'id' => $id,
        ]);

        return $address ?: null;
    }

    public function getOneById(int $id): ?DebtorExternalDataEntity
    {
        $debtorRowData = $this->getOneByIdRaw($id);

        return ($debtorRowData) ? $this->debtorExternalDataEntityFactory->createFromDatabaseRow($debtorRowData) : null;
    }
}
