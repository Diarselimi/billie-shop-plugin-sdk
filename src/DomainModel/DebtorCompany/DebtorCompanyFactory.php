<?php

namespace App\DomainModel\DebtorCompany;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressEntityFactory;

class DebtorCompanyFactory
{
    private $addressEntityFactory;

    public function __construct(AddressEntityFactory $addressEntityFactory)
    {
        $this->addressEntityFactory = $addressEntityFactory;
    }

    public function createFromAlfredResponse(array $data, bool $isStrictMatch = true): DebtorCompany
    {
        $debtorAddress = $this->addressEntityFactory->createFromOldAddressFormatResponse($data);

        return (new DebtorCompany())
            ->setId($data['id'])
            ->setUuid($data['uuid'])
            ->setName($data['name'])
            ->setDebtorAddress($debtorAddress)
            ->setCrefoId($data['crefo_id'])
            ->setSchufaId($data['schufa_id'])
            ->setIsBlacklisted($data['is_blacklisted'])
            ->setIsTrustedSource((bool) $data['is_from_trusted_source'])
            ->setIsStrictMatch($isStrictMatch)
            ->setIsSynchronized((bool) ($data['is_synchronized'] ?? null))
            ->setLegalForm($data['legal_form'] ?? null)
            ->setDebtorBillingAddresses($data['billing_addresses'] ? $this->extractBillingAddresses($data['billing_addresses']) : [])
            ->setBillingAddressMatchUuid($data['billing_address_match_uuid'])
        ;
    }

    public function createIdentifiedFromAlfredResponse(array $data, bool $isStrictMatch = true): IdentifiedDebtorCompany
    {
        $debtorAddress = $this->addressEntityFactory->createFromOldAddressFormatResponse($data);

        return (new IdentifiedDebtorCompany())
            ->setId($data['id'])
            ->setUuid($data['uuid'])
            ->setName($data['name'])
            ->setDebtorAddress($debtorAddress)
            ->setCrefoId($data['crefo_id'])
            ->setSchufaId($data['schufa_id'])
            ->setIsBlacklisted($data['is_blacklisted'])
            ->setIsTrustedSource(boolval($data['is_from_trusted_source']))
            ->setIsStrictMatch($isStrictMatch)
            ->setIsSynchronized(boolval($data['is_synchronized'] ?? null))
            ->setLegalForm($data['legal_form'] ?? null)
            ->setDebtorBillingAddresses($data['billing_addresses'] ? $this->extractBillingAddresses($data['billing_addresses']) : [])
            ->setBillingAddressMatchUuid($data['billing_address_match_uuid'])
            ->setIdentifiedAddressUuid($data['identified_address_uuid'])
            ->setIdentificationType($data['identification_type']);
    }

    /**
     * @param  array           $debtorCompanies
     * @return DebtorCompany[]
     */
    public function createFromMultipleDebtorCompaniesResponse(array $debtorCompanies): array
    {
        $responseData = [];
        foreach ($debtorCompanies['items'] as $debtorCompanyData) {
            $debtorCompany = $this->createFromAlfredResponse($debtorCompanyData);
            $responseData[$debtorCompany->getId()] = $debtorCompany;
        }

        return $responseData;
    }

    /**
     * @param  array           $data
     * @return AddressEntity[]
     */
    private function extractBillingAddresses(array $billingAddresses): array
    {
        return array_map(function ($billingAddress) {
            return $this->addressEntityFactory->createDebtorCompanyAddressFromDatabaseRow($billingAddress);
        }, $billingAddresses);
    }
}
