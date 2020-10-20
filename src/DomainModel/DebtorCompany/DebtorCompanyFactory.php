<?php

namespace App\DomainModel\DebtorCompany;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressEntityFactory;
use function Webmozart\Assert\Tests\StaticAnalysis\null;

class DebtorCompanyFactory
{
    private $addressEntityFactory;

    public function __construct(AddressEntityFactory $addressEntityFactory)
    {
        $this->addressEntityFactory = $addressEntityFactory;
    }

    public function createFromAlfredResponse(array $data, bool $isStrictMatch = true): DebtorCompany
    {
        $identifiedCompany = $data;
        $debtorAddress = $this->addressEntityFactory->createFromOldAddressFormatResponse($identifiedCompany);

        return (new DebtorCompany())
            ->setId($identifiedCompany['id'])
            ->setUuid($identifiedCompany['uuid'])
            ->setName($identifiedCompany['name'])
            ->setAddress($debtorAddress)
            ->setCrefoId($identifiedCompany['crefo_id'])
            ->setSchufaId($identifiedCompany['schufa_id'])
            ->setIsBlacklisted($identifiedCompany['is_blacklisted'])
            ->setIsTrustedSource((bool) $identifiedCompany['is_from_trusted_source'])
            ->setIsStrictMatch($isStrictMatch)
            ->setIsSynchronized((bool) ($identifiedCompany['is_synchronized'] ?? null))
            ->setLegalForm($identifiedCompany['legal_form'] ?? null)
            ->setDebtorBillingAddresses(
                $identifiedCompany['billing_addresses']
                    ? $this->extractBillingAddresses($identifiedCompany['billing_addresses'])
                    : []
            )
            ->setBillingAddressMatchUuid($identifiedCompany['billing_address_match_uuid'])
        ;
    }

    public function createIdentifiedFromAlfredResponse(array $data, bool $isStrictMatch = true): IdentifiedDebtorCompany
    {
        $identifiedCompany = $data['identified_company'];
        $debtorAddress = $this->addressEntityFactory->createFromOldAddressFormatResponse($identifiedCompany);

        return (new IdentifiedDebtorCompany())
            ->setId($identifiedCompany['id'])
            ->setUuid($identifiedCompany['uuid'])
            ->setName($identifiedCompany['name'])
            ->setAddress($debtorAddress)
            ->setCrefoId($identifiedCompany['crefo_id'])
            ->setSchufaId($identifiedCompany['schufa_id'])
            ->setIsBlacklisted($identifiedCompany['is_blacklisted'])
            ->setIsTrustedSource(boolval($identifiedCompany['is_from_trusted_source']))
            ->setIsStrictMatch($isStrictMatch)
            ->setIsSynchronized(boolval($identifiedCompany['is_synchronized'] ?? null))
            ->setLegalForm($identifiedCompany['legal_form'] ?? null)
            ->setDebtorBillingAddresses(
                $identifiedCompany['billing_addresses']
                    ? $this->extractBillingAddresses($identifiedCompany['billing_addresses'])
                    : []
            )
            ->setBillingAddressMatchUuid($identifiedCompany['billing_address_match_uuid'])
            ->setIdentifiedAddressUuid($identifiedCompany['identified_address_uuid'])
            ->setIdentificationType($identifiedCompany['identification_type']);
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
