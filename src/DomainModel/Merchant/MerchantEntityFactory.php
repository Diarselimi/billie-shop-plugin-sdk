<?php

namespace App\DomainModel\Merchant;

use App\Application\UseCase\CreateMerchant\CreateMerchantRequest;
use App\DomainModel\DebtorCompany\DebtorCompany;
use Ramsey\Uuid\Uuid;

class MerchantEntityFactory
{
    private const DEFAULT_ROLES = '["ROLE_API_USER"]';

    public function createFromDatabaseRow(array $row): MerchantEntity
    {
        return (new MerchantEntity())
            ->setId($row['id'])
            ->setName($row['name'])
            ->setApiKey($row['api_key'])
            ->setAvailableFinancingLimit($row['available_financing_limit'])
            ->setCompanyId($row['company_id'])
            ->setPaymentMerchantId($row['payment_merchant_id'])
            ->setRoles($row['roles'])
            ->setIsActive((bool) $row['is_active'])
            ->setWebhookUrl($row['webhook_url'])
            ->setWebhookAuthorization($row['webhook_authorization'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }

    public function createFromRequest(CreateMerchantRequest $request, DebtorCompany $company): MerchantEntity
    {
        return (new MerchantEntity())
            ->setCompanyId($request->getCompanyId())
            ->setAvailableFinancingLimit($request->getMerchantFinancingLimit())
            ->setWebhookUrl($request->getWebhookUrl())
            ->setWebhookAuthorization($request->getWebhookAuthorization())
            ->setName($company->getName())
            ->setApiKey(Uuid::uuid4()->toString())
            ->setRoles(self::DEFAULT_ROLES)
            ->setIsActive(true)
        ;
    }
}
