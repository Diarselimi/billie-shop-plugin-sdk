<?php

namespace App\DomainModel\DebtorCompany;

class DebtorCompanyFactory
{
    public function createFromAlfredResponse(array $data, bool $isStrictMatch = true): DebtorCompany
    {
        return (new DebtorCompany())
            ->setId($data['id'])
            ->setUuid($data['uuid'])
            ->setName($data['name'])
            ->setAddressHouse($data['address_house'])
            ->setAddressStreet($data['address_street'])
            ->setAddressPostalCode($data['address_postal_code'])
            ->setAddressCity($data['address_city'])
            ->setAddressCountry($data['address_country'])
            ->setCrefoId($data['crefo_id'])
            ->setSchufaId($data['schufa_id'])
            ->setIsBlacklisted($data['is_blacklisted'])
            ->setIsTrustedSource(boolval($data['is_from_trusted_source']))
            ->setIsStrictMatch($isStrictMatch)
            ->setIsSynchronized(boolval($data['is_synchronized'] ?? null))
        ;
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
}
