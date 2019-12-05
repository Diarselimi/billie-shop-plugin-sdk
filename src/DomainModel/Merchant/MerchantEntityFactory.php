<?php

namespace App\DomainModel\Merchant;

use App\Application\UseCase\CreateMerchant\CreateMerchantRequest;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\Helper\Uuid\UuidGeneratorInterface;

class MerchantEntityFactory
{
    private $uuidGenerator;

    public function __construct(UuidGeneratorInterface $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    public function createFromDatabaseRow(array $row): MerchantEntity
    {
        return (new MerchantEntity())
            ->setId($row['id'])
            ->setName($row['name'])
            ->setApiKey($row['api_key'])
            ->setFinancingPower($row['financing_power'])
            ->setFinancingLimit($row['available_financing_limit'])
            ->setCompanyId($row['company_id'])
            ->setPaymentUuid($row['payment_merchant_id'])
            ->setIsActive((bool) $row['is_active'])
            ->setWebhookUrl($row['webhook_url'])
            ->setWebhookAuthorization($row['webhook_authorization'])
            ->setOauthClientId($row['oauth_client_id'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }

    public function createFromRequest(CreateMerchantRequest $request, DebtorCompany $company): MerchantEntity
    {
        return (new MerchantEntity())
            ->setCompanyId($request->getCompanyId())
            ->setFinancingPower($request->getMerchantFinancingLimit())
            ->setFinancingLimit($request->getMerchantFinancingLimit())
            ->setWebhookUrl($request->getWebhookUrl())
            ->setWebhookAuthorization($request->getWebhookAuthorization())
            ->setName($company->getName())
            ->setApiKey($this->uuidGenerator->uuid4())
            ->setPaymentUuid($this->uuidGenerator->uuid4())
            ->setIsActive(true)
        ;
    }

    public function createFromCreationDTO(MerchantCreationDTO $creationDTO): MerchantEntity
    {
        return (new MerchantEntity())
            ->setCompanyId($creationDTO->getCompany()->getId())
            ->setFinancingPower($creationDTO->getMerchantFinancingLimit())
            ->setFinancingLimit($creationDTO->getMerchantFinancingLimit())
            ->setName($creationDTO->getCompany()->getName())
            ->setApiKey($creationDTO->getApiKey())
            ->setPaymentUuid($creationDTO->getPaymentUuid())
            ->setWebhookUrl($creationDTO->getWebhookUrl())
            ->setWebhookAuthorization($creationDTO->getWebhookAuthorization())
            ->setOauthClientId($creationDTO->getOauthClient()->getClientId())
            ->setIsActive(true)
        ;
    }

    /**
     * @param  array[]          $rows
     * @return MerchantEntity[]
     */
    public function createFromDatabaseRows(array $rows)
    {
        $merchants = [];

        foreach ($rows as $row) {
            $merchants[] = $this->createFromDatabaseRow($row);
        }

        return $merchants;
    }
}
